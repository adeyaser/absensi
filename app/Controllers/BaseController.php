<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'text', 'number'];

    /**
     * Current logged in user
     */
    protected $currentUser = null;

    /**
     * Current user's permissions
     */
    protected $permissions = [];

    /**
     * Session instance
     */
    protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = session();
        
        // Load current user data if logged in
        if ($this->session->has('user_id')) {
            $userModel = new \App\Models\UserModel();
            $this->currentUser = $userModel->getWithEmployee($this->session->get('user_id'));
            
            // Load permissions
            if ($this->currentUser && $this->currentUser['group_id']) {
                $permissionModel = new \App\Models\PermissionModel();
                $this->permissions = $permissionModel->getByGroup($this->currentUser['group_id']);
            }
        }
    }

    /**
     * Check if user has permission for specific action
     */
    protected function hasPermission($route, $action = 'can_view')
    {
        if (!$this->currentUser) return false;
        
        // Super admin always has access
        if ($this->currentUser['group_id'] == 1) return true;

        foreach ($this->permissions as $perm) {
            if ($perm['route'] === $route) {
                return (bool) $perm[$action];
            }
        }

        return false;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn()
    {
        return $this->session->has('user_id');
    }

    /**
     * Get current user's employee ID
     */
    protected function getEmployeeId()
    {
        return $this->currentUser['employee_id'] ?? null;
    }

    /**
     * Get menus for current user
     */
    protected function getMenus()
    {
        if (!$this->currentUser) return [];

        $menuModel = new \App\Models\MenuModel();
        return $menuModel->getMenuTree($this->currentUser['group_id']);
    }

    /**
     * Return JSON response
     */
    protected function jsonResponse($data, $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    /**
     * Return success response
     */
    protected function successResponse($data = null, $message = 'Success')
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message = 'Error', $status = 400, $errors = [])
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Prepare view data with common variables
     */
    protected function viewData($data = [])
    {
        return array_merge([
            'currentUser' => $this->currentUser,
            'menus' => $this->getMenus(),
            'permissions' => $this->permissions,
        ], $data);
    }
}
