<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeScheduleModel;
use App\Models\OfficeLocationModel;
use App\Models\LeaveRequestModel;
use App\Models\PayrollModel;
use App\Models\SettingModel;
use App\Libraries\FaceDetection;

class Mobile extends BaseController
{
    /**
     * Login API
     */
    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return $this->errorResponse('Username dan password wajib diisi');
        }

        $userModel = new UserModel();
        $user = $userModel->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        if (!$user['is_active']) {
            return $this->errorResponse('Akun tidak aktif', 401);
        }

        if (!$userModel->verifyPassword($password, $user['password'])) {
            return $this->errorResponse('Username atau password salah', 401);
        }

        // Generate token (simple implementation)
        $token = bin2hex(random_bytes(32));
        
        // Update last login
        $userModel->updateLastLogin($user['id']);

        // Get user with employee data
        $userData = $userModel->getWithEmployee($user['id']);

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'full_name' => $userData['full_name'] ?? $userData['username'],
                'employee_code' => $userData['employee_code'] ?? null,
                'employee_id' => $userData['employee_id'],
                'group_id' => $userData['group_id'],
                'avatar' => $userData['avatar'],
            ]
        ], 'Login berhasil');
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $userModel = new UserModel();
        $userData = $userModel->getWithEmployee($user['id']);

        return $this->successResponse($userData);
    }

    /**
     * Get today's attendance
     */
    public function todayAttendance()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $attendanceModel = new AttendanceModel();
        $scheduleModel = new EmployeeScheduleModel();

        $attendance = $attendanceModel->getTodayAttendance($employeeId);
        $schedule = $scheduleModel->getCurrentSchedule($employeeId);

        return $this->successResponse([
            'attendance' => $attendance,
            'schedule' => $schedule,
            'server_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Clock in
     */
    public function clockIn()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $attendanceModel = new AttendanceModel();
        
        // Check if already clocked in
        $existing = $attendanceModel->getTodayAttendance($employeeId);
        if ($existing) {
            return $this->errorResponse('Anda sudah absen masuk hari ini');
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $photo = $this->request->getPost('photo');
        $address = $this->request->getPost('address');

        $settingModel = new SettingModel();
        $settings = $settingModel->getAllAsArray();
        $locationModel = new OfficeLocationModel();

        // Validate location
        $isValidLocation = true;
        $officeLocation = null;
        
        if ($settings['attendance_require_location'] ?? false) {
            if (!$latitude || !$longitude) {
                return $this->errorResponse('Lokasi GPS diperlukan');
            }

            if ($settings['attendance_location_strict'] ?? false) {
                $officeLocation = $locationModel->checkLocation($latitude, $longitude);
                $isValidLocation = $officeLocation !== null;
            }
        }

        // Save photo
        $photoPath = null;
        if ($photo) {
            $faceDetection = new FaceDetection();
            $photoPath = $faceDetection->savePhoto($photo, $employeeId, 'clock_in');
        }

        // Calculate late
        $scheduleModel = new EmployeeScheduleModel();
        $schedule = $scheduleModel->getCurrentSchedule($employeeId);
        $lateMinutes = 0;
        $status = 'present';

        if ($schedule) {
            $scheduledClockIn = strtotime(date('Y-m-d') . ' ' . $schedule['clock_in']);
            $tolerance = ($schedule['late_tolerance'] ?? 0) * 60;
            $actualClockIn = time();

            if ($actualClockIn > ($scheduledClockIn + $tolerance)) {
                $lateMinutes = round(($actualClockIn - $scheduledClockIn) / 60);
                $status = 'late';
            }
        }

        $data = [
            'employee_id' => $employeeId,
            'date' => date('Y-m-d'),
            'clock_in' => date('Y-m-d H:i:s'),
            'clock_in_latitude' => $latitude,
            'clock_in_longitude' => $longitude,
            'clock_in_photo' => $photoPath,
            'clock_in_address' => $address,
            'office_location_id' => $officeLocation['id'] ?? null,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'is_valid_location' => $isValidLocation ? 1 : 0,
        ];

        $attendanceId = $attendanceModel->insert($data);

        return $this->successResponse([
            'attendance' => $attendanceModel->find($attendanceId),
            'late_minutes' => $lateMinutes,
            'is_valid_location' => $isValidLocation,
        ], 'Absen masuk berhasil');
    }

    /**
     * Clock out
     */
    public function clockOut()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $attendanceModel = new AttendanceModel();
        $attendance = $attendanceModel->getTodayAttendance($employeeId);
        
        if (!$attendance) {
            return $this->errorResponse('Anda belum absen masuk hari ini');
        }

        if ($attendance['clock_out']) {
            return $this->errorResponse('Anda sudah absen pulang hari ini');
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $photo = $this->request->getPost('photo');
        $address = $this->request->getPost('address');

        // Save photo
        $photoPath = null;
        if ($photo) {
            $faceDetection = new FaceDetection();
            $photoPath = $faceDetection->savePhoto($photo, $employeeId, 'clock_out');
        }

        // Calculate work hours
        $clockIn = new \DateTime($attendance['clock_in']);
        $clockOut = new \DateTime();
        $interval = $clockIn->diff($clockOut);
        $workHours = round($interval->h + ($interval->i / 60), 2);

        $data = [
            'clock_out' => date('Y-m-d H:i:s'),
            'clock_out_latitude' => $latitude,
            'clock_out_longitude' => $longitude,
            'clock_out_photo' => $photoPath,
            'clock_out_address' => $address,
            'work_hours' => $workHours,
        ];

        $attendanceModel->update($attendance['id'], $data);

        return $this->successResponse([
            'attendance' => $attendanceModel->find($attendance['id']),
            'work_hours' => $workHours,
        ], 'Absen pulang berhasil');
    }

    /**
     * Get attendance history
     */
    public function attendanceHistory()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');

        $attendanceModel = new AttendanceModel();
        $attendances = $attendanceModel->getByMonth($employeeId, $month, $year);
        $recap = $attendanceModel->getMonthlyRecap($employeeId, $month, $year);

        return $this->successResponse([
            'attendances' => $attendances,
            'recap' => $recap,
        ]);
    }

    /**
     * Get leave balance
     */
    public function leaveBalance()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $leaveRequestModel = new LeaveRequestModel();
        $balance = $leaveRequestModel->getLeaveBalance($employeeId, date('Y'));

        return $this->successResponse($balance);
    }

    /**
     * Get payslips
     */
    public function payslips()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $user['employee_id'];
        if (!$employeeId) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        $payrollModel = new PayrollModel();
        $payrolls = $payrollModel->getEmployeePayrollHistory($employeeId);

        return $this->successResponse($payrolls);
    }

    /**
     * Get office locations
     */
    public function officeLocations()
    {
        $locationModel = new OfficeLocationModel();
        $locations = $locationModel->getActive();

        return $this->successResponse($locations);
    }

    /**
     * Update face encoding
     */
    public function updateFaceEncoding()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $faceEncoding = $this->request->getPost('face_encoding');
        
        if (!$faceEncoding) {
            return $this->errorResponse('Face encoding diperlukan');
        }

        $userModel = new UserModel();
        $userModel->update($user['id'], ['face_encoding' => $faceEncoding]);

        return $this->successResponse(null, 'Data wajah berhasil disimpan');
    }

    /**
     * Helper: Get authenticated user from request
     */
    protected function getAuthUser()
    {
        // Get user from session (for web) or from token header (for API)
        if ($this->session->has('user_id')) {
            $userModel = new UserModel();
            return $userModel->find($this->session->get('user_id'));
        }

        // Check for Authorization header
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            // For production, you should validate the token
            // This is a simplified implementation
            $token = substr($authHeader, 7);
            // Validate token and get user...
        }

        return null;
    }
}
