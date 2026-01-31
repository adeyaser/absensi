<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRequestModel;
use App\Models\PayrollModel;
use App\Models\SettingModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $attendanceModel = new AttendanceModel();
        $employeeModel = new EmployeeModel();
        $leaveRequestModel = new LeaveRequestModel();
        $payrollModel = new PayrollModel();
        $settingModel = new SettingModel();

        $today = date('Y-m-d');
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get statistics based on user role
        $stats = [];
        
        if ($this->currentUser['group_id'] == 1 || $this->currentUser['group_id'] == 2) {
            // Admin/HRD stats
            $stats['total_employees'] = $employeeModel->where('is_active', 1)->countAllResults();
            $stats['present_today'] = $attendanceModel->where('date', $today)
                ->whereIn('status', ['present', 'late'])
                ->countAllResults();
            $stats['absent_today'] = $stats['total_employees'] - $stats['present_today'];
            $stats['pending_leaves'] = $leaveRequestModel->where('status', 'pending')->countAllResults();
            
            // Today's attendance
            $todayAttendance = $attendanceModel->getDailyReport($today);
        } else {
            // Employee stats
            $employeeId = $this->getEmployeeId();
            if ($employeeId) {
                $monthlyRecap = $attendanceModel->getMonthlyRecap($employeeId, $currentMonth, $currentYear);
                $stats['present_days'] = $monthlyRecap['present'];
                $stats['late_days'] = $monthlyRecap['late'];
                $stats['leave_days'] = $monthlyRecap['leave'];
                
                // Leave balance
                $leaveBalance = $leaveRequestModel->getLeaveBalance($employeeId, $currentYear);
                $stats['leave_balance'] = $leaveBalance;

                // Pending subordinates' leaves
                $stats['subordinate_pending_leaves'] = $leaveRequestModel->join('employees', 'employees.id = leave_requests.employee_id')
                    ->where('employees.supervisor_id', $employeeId)
                    ->where('leave_requests.status', 'pending')
                    ->countAllResults();
            }
            $todayAttendance = null;
        }

        // Get today's attendance for current user
        $myAttendance = null;
        if ($this->getEmployeeId()) {
            $myAttendance = $attendanceModel->getTodayAttendance($this->getEmployeeId());
        }

        // Get settings
        $settings = $settingModel->getAllAsArray();

        return view('dashboard/index', $this->viewData([
            'title' => 'Dashboard',
            'stats' => $stats,
            'todayAttendance' => $todayAttendance,
            'myAttendance' => $myAttendance,
            'settings' => $settings,
        ]));
    }

    public function getStats()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $attendanceModel = new AttendanceModel();
        $today = date('Y-m-d');

        // Get hourly attendance data for chart
        $hourlyData = [];
        for ($i = 6; $i <= 18; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $count = $attendanceModel->where('date', $today)
                ->where("HOUR(clock_in) = {$i}")
                ->countAllResults();
            $hourlyData[] = [
                'hour' => $hour . ':00',
                'count' => $count
            ];
        }

        return $this->successResponse([
            'hourly_attendance' => $hourlyData
        ]);
    }
}
