<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeScheduleModel;
use App\Models\OfficeLocationModel;
use App\Models\SettingModel;
use App\Models\OvertimeRequestModel;
use App\Models\EmployeeModel;
use App\Libraries\FaceDetection;

class Attendance extends BaseController
{
    protected $attendanceModel;
    protected $scheduleModel;
    protected $locationModel;
    protected $settingModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->scheduleModel = new EmployeeScheduleModel();
        $this->locationModel = new OfficeLocationModel();
        $this->settingModel = new SettingModel();
    }

    public function clock()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employeeId = $this->getEmployeeId();
        
        // Show info message for admin without employee_id
        if (!$employeeId && in_array($this->currentUser['group_id'], [1, 2])) {
            // Admin can view the page but cannot do clock in/out
            $todayAttendance = null;
            $schedule = null;
        } elseif (!$employeeId) {
            return redirect()->to('/dashboard')->with('error', 'Akun Anda tidak terhubung dengan data pegawai');
        } else {
            $todayAttendance = $this->attendanceModel->getTodayAttendance($employeeId);
            $schedule = $this->scheduleModel->getCurrentSchedule($employeeId);
        }
        $settings = $this->settingModel->getAllAsArray();
        $locations = $this->locationModel->getActive();

        return view('attendance/clock', $this->viewData([
            'title' => 'Absen Masuk/Pulang',
            'todayAttendance' => $todayAttendance,
            'schedule' => $schedule,
            'settings' => $settings,
            'locations' => $locations,
        ]));
    }

    public function clockIn()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $this->getEmployeeId();
        
        // Only check employee_id for non-admin users (group_id != 1 and != 2)
        if (!$employeeId && !in_array($this->currentUser['group_id'], [1, 2])) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        // Admin without employee_id cannot clock in/out
        if (!$employeeId) {
            return $this->errorResponse('Admin tidak dapat melakukan absensi tanpa data pegawai terhubung');
        }

        // Check if already clocked in
        $existing = $this->attendanceModel->getTodayAttendance($employeeId);
        if ($existing) {
            return $this->errorResponse('Anda sudah absen masuk hari ini');
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $photo = $this->request->getPost('photo');
        $address = $this->request->getPost('address');

        $settings = $this->settingModel->getAllAsArray();

        // Validate location if required
        $isValidLocation = true;
        $officeLocation = null;
        
        if ($settings['attendance_require_location'] ?? false) {
            if (!$latitude || !$longitude) {
                return $this->errorResponse('Lokasi GPS diperlukan');
            }

            if ($settings['attendance_location_strict'] ?? false) {
                $officeLocation = $this->locationModel->checkLocation($latitude, $longitude);
                if (!$officeLocation) {
                    $nearest = $this->locationModel->getNearestLocation($latitude, $longitude);
                    $isValidLocation = false;
                    // Still allow but mark as invalid location
                }
            }
        }

        // Validate photo if required
        $isValidFace = true;
        $photoPath = null;
        
        if ($settings['attendance_require_photo'] ?? false) {
            if (!$photo) {
                return $this->errorResponse('Foto selfie diperlukan');
            }

            $faceDetection = new FaceDetection();
            
            // Check image quality
            $qualityCheck = $faceDetection->validateImageQuality($photo);
            if (!$qualityCheck['valid']) {
                return $this->errorResponse($qualityCheck['message']);
            }

            // Save photo
            $photoPath = $faceDetection->savePhoto($photo, $employeeId, 'clock_in');

            // Validate face if required
            if ($settings['attendance_require_face'] ?? false) {
                $faceCheck = $faceDetection->detectFace($photo);
                if (!$faceCheck['detected']) {
                    return $this->errorResponse('Wajah tidak terdeteksi, silakan foto ulang');
                }
            }
        }

        // Get schedule for late calculation
        $schedule = $this->scheduleModel->getCurrentSchedule($employeeId);
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
            'is_valid_face' => $isValidFace ? 1 : 0,
        ];

        $attendanceId = $this->attendanceModel->insert($data);

        if (!$attendanceId) {
            return $this->errorResponse('Gagal menyimpan data absensi');
        }

        $message = 'Absen masuk berhasil';
        if ($status === 'late') {
            $message .= ". Anda terlambat {$lateMinutes} menit";
        }
        if (!$isValidLocation) {
            $message .= ". Lokasi di luar area kantor";
        }

        return $this->successResponse([
            'attendance' => $this->attendanceModel->find($attendanceId),
            'late_minutes' => $lateMinutes,
            'is_valid_location' => $isValidLocation,
        ], $message);
    }

    public function clockOut()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $employeeId = $this->getEmployeeId();
        
        // Only check employee_id for non-admin users (group_id != 1 and != 2)
        if (!$employeeId && !in_array($this->currentUser['group_id'], [1, 2])) {
            return $this->errorResponse('Akun tidak terhubung dengan data pegawai');
        }

        // Admin without employee_id cannot clock in/out
        if (!$employeeId) {
            return $this->errorResponse('Admin tidak dapat melakukan absensi tanpa data pegawai terhubung');
        }

        // Check if clocked in
        $attendance = $this->attendanceModel->getTodayAttendance($employeeId);
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

        $settings = $this->settingModel->getAllAsArray();

        // Validate photo if required
        $photoPath = null;
        if ($settings['attendance_require_photo'] ?? false) {
            if ($photo) {
                $faceDetection = new FaceDetection();
                $photoPath = $faceDetection->savePhoto($photo, $employeeId, 'clock_out');
            }
        }

        // Calculate work hours
        $clockIn = new \DateTime($attendance['clock_in']);
        $clockOut = new \DateTime();
        $interval = $clockIn->diff($clockOut);
        $workHours = round($interval->h + ($interval->i / 60), 2);

        // Calculate early leave
        $earlyLeaveMinutes = 0;
        $schedule = $this->scheduleModel->getCurrentSchedule($employeeId);
        
        if ($schedule) {
            $scheduledClockOut = strtotime(date('Y-m-d') . ' ' . $schedule['clock_out']);
            $tolerance = ($schedule['early_leave_tolerance'] ?? 0) * 60;
            $actualClockOut = time();

            if ($actualClockOut < ($scheduledClockOut - $tolerance)) {
                $earlyLeaveMinutes = round(($scheduledClockOut - $actualClockOut) / 60);
            }
        }

        $data = [
            'clock_out' => date('Y-m-d H:i:s'),
            'clock_out_latitude' => $latitude,
            'clock_out_longitude' => $longitude,
            'clock_out_photo' => $photoPath,
            'clock_out_address' => $address,
            'work_hours' => $workHours,
            'early_leave_minutes' => $earlyLeaveMinutes,
        ];

        // Update status if early leave
        if ($earlyLeaveMinutes > 0 && $attendance['status'] !== 'late') {
            $data['status'] = 'early_leave';
        }

        $this->attendanceModel->update($attendance['id'], $data);

        $message = "Absen pulang berhasil. Total jam kerja: {$workHours} jam";
        if ($earlyLeaveMinutes > 0) {
            $message .= ". Pulang lebih awal {$earlyLeaveMinutes} menit";
        }

        return $this->successResponse([
            'attendance' => $this->attendanceModel->find($attendance['id']),
            'work_hours' => $workHours,
            'early_leave_minutes' => $earlyLeaveMinutes,
        ], $message);
    }

    public function history()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employeeId = $this->getEmployeeId();
        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');

        $attendances = [];
        $recap = null;

        if ($employeeId) {
            $attendances = $this->attendanceModel->getByMonth($employeeId, $month, $year);
            $recap = $this->attendanceModel->getMonthlyRecap($employeeId, $month, $year);
        }

        return view('attendance/history', $this->viewData([
            'title' => 'Riwayat Absensi',
            'attendances' => $attendances,
            'recap' => $recap,
            'month' => $month,
            'year' => $year,
        ]));
    }

    public function recap()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $month = (int) ($this->request->getGet('month') ?? date('m'));
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $recaps = $this->attendanceModel->getAllEmployeeRecap($month, $year);

        // Number of days in the selected month/year
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return view('attendance/recap', $this->viewData([
            'title' => 'Rekap Absensi',
            'recaps' => $recaps,
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $daysInMonth,
        ]));
    }

    public function report($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $attendance = $this->attendanceModel->find($id);
        
        if (!$attendance) {
            return redirect()->to('/attendance/history')->with('error', 'Data tidak ditemukan');
        }

        return view('attendance/report', $this->viewData([
            'title' => 'Detail Absensi',
            'attendance' => $attendance,
        ]));
    }

    public function manual()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'employee_id' => $this->request->getPost('employee_id'),
                'date' => $this->request->getPost('date'),
                'clock_in' => $this->request->getPost('date') . ' ' . $this->request->getPost('clock_in'),
                'clock_out' => $this->request->getPost('clock_out') ? $this->request->getPost('date') . ' ' . $this->request->getPost('clock_out') : null,
                'status' => $this->request->getPost('status'),
                'notes' => $this->request->getPost('notes'),
            ];

            // Calculate work hours if clock out provided
            if ($data['clock_out']) {
                $clockIn = new \DateTime($data['clock_in']);
                $clockOut = new \DateTime($data['clock_out']);
                $interval = $clockIn->diff($clockOut);
                $data['work_hours'] = round($interval->h + ($interval->i / 60), 2);
            }

            $this->attendanceModel->insert($data);

            return redirect()->to('/attendance/recap')->with('success', 'Data absensi berhasil ditambahkan');
        }

        $employeeModel = new \App\Models\EmployeeModel();
        $employees = $employeeModel->getActive();

        return view('attendance/manual', $this->viewData([
            'title' => 'Input Absensi Manual',
            'employees' => $employees,
        ]));
    }

    public function export()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');

        $recaps = $this->attendanceModel->getAllEmployeeRecap($month, $year);

        // Generate CSV
        $filename = 'attendance_recap_' . $year . $month . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['Kode', 'Nama', 'Departemen', 'Hadir', 'Terlambat', 'Tidak Hadir', 'Cuti', 'Sakit', 'Total Jam', 'Total Terlambat (menit)', 'Total Lembur (menit)']);
        
        foreach ($recaps as $recap) {
            fputcsv($output, [
                $recap['employee_code'],
                $recap['full_name'],
                $recap['department_name'],
                $recap['present_days'],
                $recap['late_days'],
                $recap['absent_days'],
                $recap['leave_days'],
                $recap['sick_days'],
                $recap['total_work_hours'],
                $recap['total_late_minutes'],
                $recap['total_overtime_minutes'],
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Overtime Management
     */
    public function overtime()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $overtimeModel = new OvertimeRequestModel();
        $employeeModel = new EmployeeModel();
        $userModel = new \App\Models\UserModel();

        // Get overtimes with employee supervisor data
        if (session('group_id') <= 2) {
            // Admin/HR can see all
            $overtimes = $overtimeModel->getWithEmployee();
            
            // Get employees with their supervisor info
            $employees = $employeeModel
                ->select('employees.id, employees.full_name, sup_user.id as supervisor_user_id')
                ->join('employees as supervisor', 'supervisor.id = employees.supervisor_id', 'left')
                ->join('users as sup_user', 'sup_user.employee_id = supervisor.id', 'left')
                ->where('employees.is_active', 1)
                ->findAll();
        } else {
            // Employee sees only their own
            $employeeId = $this->getEmployeeId();
            $overtimes = $overtimeModel
                ->select('overtime_requests.*, 
                    employees.full_name as employee_name, 
                    employees.employee_code,
                    employees.supervisor_id,
                    departments.name as department_name,
                    emp_supervisor.full_name as employee_supervisor_name,
                    designated_sup.username as designated_supervisor_name,
                    designated_fin.username as designated_finance_name,
                    supervisor.username as supervisor_name,
                    finance.username as finance_name')
                ->join('employees', 'employees.id = overtime_requests.employee_id')
                ->join('departments', 'departments.id = employees.department_id', 'left')
                ->join('employees as emp_supervisor', 'emp_supervisor.id = employees.supervisor_id', 'left')
                ->join('users as designated_sup', 'designated_sup.id = overtime_requests.designated_supervisor_id', 'left')
                ->join('users as designated_fin', 'designated_fin.id = overtime_requests.designated_finance_id', 'left')
                ->join('users as supervisor', 'supervisor.id = overtime_requests.approved_by_supervisor', 'left')
                ->join('users as finance', 'finance.id = overtime_requests.approved_by_finance', 'left')
                ->where('overtime_requests.employee_id', $employeeId)
                ->orderBy('overtime_requests.created_at', 'DESC')
                ->findAll();
            $employees = [];
        }

        // Get list of users who can approve (Admin, HR, Manager)
        $approvers = $userModel
            ->select('users.*, groups.name as group_name')
            ->join('groups', 'groups.id = users.group_id', 'left')
            ->whereIn('users.group_id', [1, 2, 3]) // Admin, HR, Manager
            ->where('users.is_active', 1)
            ->findAll();

        // Calculate stats
        $stats = [
            'total' => count($overtimes),
            'pending' => 0,
            'approved' => 0,
            'total_hours' => 0,
        ];

        foreach ($overtimes as $ot) {
            if (in_array($ot['status'], ['pending', 'pending_finance'])) $stats['pending']++;
            if ($ot['status'] === 'approved') {
                $stats['approved']++;
                $stats['total_hours'] += $ot['duration_hours'];
            }
        }

        return view('attendance/overtime', $this->viewData([
            'title' => 'Pengajuan Lembur',
            'overtimes' => $overtimes,
            'employees' => $employees,
            'approvers' => $approvers,
            'stats' => $stats,
        ]));
    }

    public function storeOvertime()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $overtimeModel = new OvertimeRequestModel();

        $employeeId = $this->request->getPost('employee_id') ?? $this->getEmployeeId();
        $date = $this->request->getPost('date');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');

        // Calculate duration
        $start = new \DateTime($date . ' ' . $startTime);
        $end = new \DateTime($date . ' ' . $endTime);
        $interval = $start->diff($end);
        $duration = round($interval->h + ($interval->i / 60), 2);

        $data = [
            'employee_id' => $employeeId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $duration,
            'reason' => $this->request->getPost('reason'),
            'status' => 'pending',
        ];

        // Save designated approvers
        $supervisorId = $this->request->getPost('approver_supervisor_id');
        $financeId = $this->request->getPost('approver_finance_id');
        
        // If not provided (by regular employee, or skipped by Admin)
        if (empty($supervisorId)) {
            $employeeModel = new EmployeeModel();
            $emp = $employeeModel
                ->select('sup_user.id as supervisor_user_id')
                ->join('employees as supervisor', 'supervisor.id = employees.supervisor_id', 'left')
                ->join('users as sup_user', 'sup_user.employee_id = supervisor.id', 'left')
                ->find($employeeId);
            
            if ($emp && !empty($emp['supervisor_user_id'])) {
                $supervisorId = $emp['supervisor_user_id'];
            }
        }
        
        if (empty($financeId)) {
            $userModel = new \App\Models\UserModel();
            $finance = $userModel->where('group_id', 2)->where('is_active', 1)->first();
            if ($finance) {
                $financeId = $finance['id'];
            }
        }

        if (!empty($supervisorId)) {
            $data['designated_supervisor_id'] = $supervisorId;
        }
        if (!empty($financeId)) {
            $data['designated_finance_id'] = $financeId;
        }

        $overtimeModel->insert($data);

        return redirect()->to('/attendance/overtime')->with('success', 'Pengajuan lembur berhasil diajukan');
    }

    public function approveOvertime($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $overtimeModel = new OvertimeRequestModel();
        $overtime = $overtimeModel->find($id);

        if (!$overtime) {
            return redirect()->to('/attendance/overtime')->with('error', 'Data tidak ditemukan');
        }

        $userId = session('user_id');
        $groupId = $this->currentUser['group_id'];

        if ($overtime['status'] === 'pending') {
            // Supervisor Approval (assuming group 1, 2, or others authorized)
            // For now, allow admin/hr (1,2) or supervisor-like roles to approve first step
            $overtimeModel->approveSupervisor($id, $userId);
            return redirect()->to('/attendance/overtime')->with('success', 'Pengajuan lembur disetujui (Menunggu persetujuan Keuangan)');
        } 
        elseif ($overtime['status'] === 'pending_finance') {
            // Finance Approval (assuming group 1, 2)
            // Ideally check if user is specifically Finance/HR
            if ($groupId <= 2) {
                $overtimeModel->approveFinance($id, $userId);
                return redirect()->to('/attendance/overtime')->with('success', 'Pengajuan lembur disetujui sepenuhnya');
            } else {
                return redirect()->to('/attendance/overtime')->with('error', 'Anda tidak memiliki akses untuk persetujuan akhir');
            }
        }

        return redirect()->to('/attendance/overtime');
    }

    public function rejectOvertime($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $reason = $this->request->getGet('reason');
        $overtimeModel = new OvertimeRequestModel();
        $overtimeModel->update($id, [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/attendance/overtime')->with('success', 'Pengajuan lembur ditolak');
    }

    public function cancelOvertime($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $overtimeModel = new OvertimeRequestModel();
        $overtimeModel->update($id, ['status' => 'cancelled']);

        return redirect()->to('/attendance/overtime')->with('success', 'Pengajuan lembur dibatalkan');
    }

    public function storeManual()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $data = [
            'employee_id' => $this->request->getPost('employee_id'),
            'date' => $this->request->getPost('date'),
            'clock_in' => $this->request->getPost('clock_in') ? $this->request->getPost('date') . ' ' . $this->request->getPost('clock_in') . ':00' : null,
            'clock_out' => $this->request->getPost('clock_out') ? $this->request->getPost('date') . ' ' . $this->request->getPost('clock_out') . ':00' : null,
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes'),
        ];

        // Calculate work hours if clock out provided
        if ($data['clock_in'] && $data['clock_out']) {
            $clockIn = new \DateTime($data['clock_in']);
            $clockOut = new \DateTime($data['clock_out']);
            $interval = $clockIn->diff($clockOut);
            $data['work_hours'] = round($interval->h + ($interval->i / 60), 2);
        }

        // Check if record exists for this date and employee
        $existing = $this->attendanceModel
            ->where('employee_id', $data['employee_id'])
            ->where('date', $data['date'])
            ->first();

        if ($existing) {
            $this->attendanceModel->update($existing['id'], $data);
            $message = 'Data absensi berhasil diperbarui';
        } else {
            $this->attendanceModel->insert($data);
            $message = 'Data absensi berhasil ditambahkan';
        }

        return redirect()->to('/attendance/recap')->with('success', $message);
    }
}
