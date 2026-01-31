<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PermissionModel;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        if (!$session->has('user_id')) {
            return redirect()->to('/auth');
        }

        // Super admin always has access
        $groupId = $session->get('group_id');
        if ($groupId == 1) {
            return $request;
        }

        // Get current route
        $router = service('router');
        $controllerName = strtolower($router->controllerName());
        $controllerName = str_replace('\\', '/', $controllerName);
        $parts = explode('/', $controllerName);
        $route = end($parts);

        // Get method
        $method = $request->getMethod();
        $action = 'can_view';
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            // Check if this is create or update
            $routerMethod = $router->methodName();
            if (in_array($routerMethod, ['store', 'create', 'save'])) {
                $action = 'can_create';
            } else {
                $action = 'can_edit';
            }
        } elseif ($method === 'DELETE') {
            $action = 'can_delete';
        }

        // Check permission
        $permissionModel = new PermissionModel();
        $hasAccess = $permissionModel->hasPermission($groupId, $route, $action);

        if (!$hasAccess) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['success' => false, 'message' => 'Access denied']);
            }
            
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
