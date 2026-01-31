<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        // Allow both GET redirect and POST login
        if ($this->request->getMethod() === 'GET' || strtolower($this->request->getMethod()) === 'get') {
            return redirect()->to('/auth');
        }

        // Verify Turnstile
        $turnstileResponse = $this->request->getPost('cf-turnstile-response');
        if (!$this->verifyTurnstile($turnstileResponse)) {
            return redirect()->back()->withInput()->with('error', 'Verifikasi keamanan gagal. Silakan coba lagi.');
        }

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Find user by username or email
        $user = $this->userModel->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah');
        }

        if (!$user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda tidak aktif');
        }

        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah');
        }

        // Set session
        $this->session->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'group_id' => $user['group_id'],
            'employee_id' => $user['employee_id'],
            'is_logged_in' => true,
        ]);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['username']);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/auth')->with('success', 'Anda telah logout');
    }

    public function profile()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $user = $this->userModel->getWithEmployee($this->session->get('user_id'));
        
        return view('auth/profile', $this->viewData([
            'title' => 'Profile',
            'user' => $user,
        ]));
    }

    public function updateProfile()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $userId = $this->session->get('user_id');
        
        $rules = [
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]",
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'email' => $this->request->getPost('email'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        // Handle avatar upload
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $userId . '_' . $avatar->getRandomName();
            $avatar->move(WRITEPATH . 'uploads/avatars', $newName);
            $data['avatar'] = 'avatars/' . $newName;
        }

        $this->userModel->update($userId, $data);

        return redirect()->back()->with('success', 'Profile berhasil diupdate');
    }

    public function updateFaceData()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $faceEncoding = $this->request->getPost('face_encoding');
        
        if (!$faceEncoding) {
            return $this->errorResponse('Face encoding required');
        }

        $this->userModel->update($this->session->get('user_id'), [
            'face_encoding' => $faceEncoding
        ]);

        return $this->successResponse(null, 'Data wajah berhasil disimpan');
    }

    /**
     * Verify Cloudflare Turnstile response
     */
    private function verifyTurnstile(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $secretKey = '0x4AAAAAACQLw05XFUCIx7KmA_3-48gpuVw';
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $this->request->getIPAddress(),
                ]
            ]);

            $result = json_decode($response->getBody());
            
            if ($result && isset($result->success) && $result->success === true) {
                return true;
            }
            
            // Log error for debugging
            log_message('warning', 'Turnstile verification failed: ' . json_encode($result));
            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Turnstile verification error: ' . $e->getMessage());
            return false;
        }
    }
}
