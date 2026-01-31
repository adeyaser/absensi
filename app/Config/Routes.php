<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// PUBLIC ROUTES
// =====================================================
$routes->get('/', 'Auth::index');
$routes->get('auth', 'Auth::index');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('writable/uploads/(:any)', 'Uploads::index/$1');

// =====================================================
// PROTECTED ROUTES (require authentication)
// =====================================================
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/stats', 'Dashboard::getStats');
    
    // Profile
    $routes->get('profile', 'Auth::profile');
    $routes->post('profile/update', 'Auth::updateProfile');
    $routes->post('profile/face', 'Auth::updateFaceData');
    
    // =====================================================
    // EMPLOYEES
    // =====================================================
    $routes->get('employees', 'Employees::index');
    $routes->get('employees/create', 'Employees::create');
    $routes->post('employees/store', 'Employees::store');
    $routes->get('employees/(:num)', 'Employees::show/$1');
    $routes->get('employees/edit/(:num)', 'Employees::edit/$1');
    $routes->post('employees/update/(:num)', 'Employees::update/$1');
    $routes->delete('employees/(:num)', 'Employees::delete/$1');
    $routes->get('employees/positions/(:num)', 'Employees::getPositions/$1');
    $routes->get('employees/search', 'Employees::search');
    $routes->get('employees/export', 'Employees::export');
    
    // =====================================================
    // ATTENDANCE
    // =====================================================
    $routes->get('attendance/clock', 'Attendance::clock');
    $routes->post('attendance/clock-in', 'Attendance::clockIn');
    $routes->post('attendance/clock-out', 'Attendance::clockOut');
    $routes->get('attendance/history', 'Attendance::history');
    $routes->get('attendance/recap', 'Attendance::recap');
    $routes->get('attendance/report/(:num)', 'Attendance::report/$1');
    $routes->get('attendance/manual', 'Attendance::manual');
    $routes->post('attendance/manual', 'Attendance::storeManual');
    $routes->get('attendance/export', 'Attendance::export');
    
    // Overtime
    $routes->get('attendance/overtime', 'Attendance::overtime');
    $routes->post('attendance/overtime/store', 'Attendance::storeOvertime');
    $routes->get('attendance/overtime/approve/(:num)', 'Attendance::approveOvertime/$1');
    $routes->get('attendance/overtime/reject/(:num)', 'Attendance::rejectOvertime/$1');
    $routes->get('attendance/overtime/cancel/(:num)', 'Attendance::cancelOvertime/$1');
    
    // =====================================================
    // LEAVE
    // =====================================================
    $routes->get('leave', 'Leave::index');
    $routes->get('leave/create', 'Leave::create');
    $routes->get('leave/(:num)', 'Leave::show/$1');
    $routes->post('leave/store', 'Leave::store');
    $routes->get('leave/approval', 'Leave::approval');
    $routes->post('leave/approve/(:num)', 'Leave::approve/$1');
    $routes->post('leave/reject/(:num)', 'Leave::reject/$1');
    $routes->post('leave/cancel/(:num)', 'Leave::cancel/$1');
    $routes->get('leave/history', 'Leave::history');
    
    // =====================================================
    // PAYROLL
    // =====================================================
    $routes->get('payroll', 'Payroll::index');
    $routes->get('payroll/(:num)', 'Payroll::show/$1');
    $routes->get('payroll/process', 'Payroll::process');
    $routes->post('payroll/calculate', 'Payroll::calculate');
    $routes->get('payroll/show/(:num)', 'Payroll::show/$1');
    $routes->get('payroll/slip/(:num)', 'Payroll::slip/$1');
    $routes->post('payroll/approve', 'Payroll::approve');
    $routes->post('payroll/pay', 'Payroll::pay');
    $routes->get('payroll/my-slips', 'Payroll::mySlips');
    $routes->get('payroll/components', 'Payroll::components');
    $routes->post('payroll/save-component', 'Payroll::saveComponent');
    $routes->get('payroll/employee-salary', 'Payroll::employeeSalary');
    $routes->get('payroll/employee-salary/edit/(:num)', 'Payroll::editEmployeeSalary/$1');
    $routes->post('payroll/update-employee-salary', 'Payroll::updateEmployeeSalary');
    $routes->get('payroll/export', 'Payroll::export');
    
    // =====================================================
    // MASTER DATA
    // =====================================================
    
    // Departments
    $routes->get('master/departments', 'Master::departments');
    $routes->post('master/save-department', 'Master::saveDepartment');
    $routes->delete('master/department/(:num)', 'Master::deleteDepartment/$1');
    
    // Positions
    $routes->get('master/positions', 'Master::positions');
    $routes->get('master/positions/create', 'Master::createPosition');
    $routes->post('master/positions/store', 'Master::savePosition');
    $routes->get('master/positions/edit/(:num)', 'Master::editPosition/$1');
    $routes->post('master/positions/update/(:num)', 'Master::savePosition/$1');
    $routes->delete('master/position/(:num)', 'Master::deletePosition/$1');
    
    // Schedules
    $routes->get('master/schedules', 'Master::schedules');
    $routes->post('master/save-schedule', 'Master::saveSchedule');
    $routes->delete('master/schedule/(:num)', 'Master::deleteSchedule/$1');
    
    // Locations
    $routes->get('master/locations', 'Master::locations');
    $routes->post('master/save-location', 'Master::saveLocation');
    $routes->delete('master/location/(:num)', 'Master::deleteLocation/$1');
    
    // Holidays
    $routes->get('master/holidays/sync', 'Master::syncHolidays');
    $routes->get('master/holidays', 'Master::holidays');
    $routes->post('master/save-holiday', 'Master::saveHoliday');
    $routes->post('master/holidays/store', 'Master::saveHoliday'); // Alias for modal
    $routes->post('master/holidays/update/(:num)', 'Master::saveHoliday'); // Alias for modal
    $routes->delete('master/holidays/(:num)', 'Master::deleteHoliday/$1'); // Alias for table
    $routes->delete('master/holiday/(:num)', 'Master::deleteHoliday/$1');
    
    // Leave Types
    $routes->get('master/leave-types', 'Master::leaveTypes');
    $routes->post('master/save-leave-type', 'Master::saveLeaveType');
    $routes->delete('master/leave-type/(:num)', 'Master::deleteLeaveType/$1');
    
    // =====================================================
    // SETTINGS
    // =====================================================
    $routes->get('settings', 'Settings::general');
    $routes->post('settings/save', 'Settings::saveGeneral');
    
    // Users
    $routes->get('settings/users', 'Settings::users');
    // Backwards-compatible endpoints (old view/scripts may still post to these)
    $routes->post('settings/users/store', 'Settings::saveUser');
    $routes->post('settings/users/update/(:num)', 'Settings::saveUser');
    $routes->post('settings/save-user', 'Settings::saveUser');
    $routes->delete('settings/user/(:num)', 'Settings::deleteUser/$1');
    $routes->get('settings/employees', 'Settings::getEmployees');
    
    // Groups
    $routes->get('settings/groups', 'Settings::groups');
    $routes->post('settings/save-group', 'Settings::saveGroup');
    // Leave Config
    $routes->get('leave-config', 'LeaveConfig::index');
    $routes->get('leave-config/edit/(:num)', 'LeaveConfig::edit/$1');
    $routes->post('leave-config/update/(:num)', 'LeaveConfig::update/$1');

    
    // Menus
    $routes->get('settings/menus', 'Settings::menus');
    $routes->post('settings/save-menu', 'Settings::saveMenu');
    
    // Permissions
    $routes->get('settings/permissions', 'Settings::permissions');
    $routes->post('settings/save-permissions', 'Settings::savePermissions');
    
    // =====================================================
    // REPORTS
    // =====================================================
    $routes->get('reports/attendance', 'Reports::attendance');
    $routes->get('reports/payroll', 'Reports::payroll');
    $routes->get('reports/employees', 'Reports::employees');
});

// =====================================================
// MOBILE API ROUTES
// =====================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    
    // Public endpoints
    $routes->post('login', 'Mobile::login');
    
    // Protected endpoints
    $routes->group('', ['filter' => 'auth'], function ($routes) {
        $routes->get('profile', 'Mobile::profile');
        $routes->get('today-attendance', 'Mobile::todayAttendance');
        $routes->post('clock-in', 'Mobile::clockIn');
        $routes->post('clock-out', 'Mobile::clockOut');
        $routes->get('attendance-history', 'Mobile::attendanceHistory');
        $routes->get('leave-balance', 'Mobile::leaveBalance');
        $routes->get('payslips', 'Mobile::payslips');
        $routes->get('office-locations', 'Mobile::officeLocations');
        $routes->post('update-face', 'Mobile::updateFaceEncoding');
    });
});

