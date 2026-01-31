<?php

namespace App\Controllers;

use App\Models\LeaveRequestModel;
use App\Models\LeaveTypeModel;
use App\Models\AttendanceModel;

class Leave extends BaseController
{
    protected $leaveRequestModel;
    protected $leaveTypeModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
        $this->leaveTypeModel = new LeaveTypeModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employeeId = $this->getEmployeeId();
        
        $requests = [];
        $balance = [];
        
        if ($employeeId) {
            $requests = $this->leaveRequestModel->getByEmployee($employeeId, date('Y'));
            $balance = $this->leaveRequestModel->getLeaveBalance($employeeId, date('Y'));
        }

        return view('leave/index', $this->viewData([
            'title' => 'Pengajuan Cuti',
            'leaves' => $requests,
            'leaveBalance' => $balance,
        ]));
    }

    public function show($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $request = $this->leaveRequestModel->getWithDetails($id);
        
        if (!$request) {
            return redirect()->to('/leave')->with('error', 'Data pengajuan cuti tidak ditemukan');
        }

        // Security check: regular employees can only see their own requests
        $employeeId = $this->getEmployeeId();
        if ($this->currentUser['group_id'] > 2 && $request['employee_id'] != $employeeId) {
            return redirect()->to('/leave')->with('error', 'Akses ditolak');
        }

        return view('leave/show', $this->viewData([
            'title' => 'Detail Pengajuan Cuti',
            'request' => $request,
        ]));
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $leaveTypes = $this->leaveTypeModel->getActive();
        $employeeId = $this->getEmployeeId();
        $balance = $this->leaveRequestModel->getLeaveBalance($employeeId, date('Y'));

        return view('leave/form', $this->viewData([
            'title' => 'Ajukan Cuti',
            'leaveTypes' => $leaveTypes,
            'leaveBalance' => $balance,
            'request' => null,
        ]));
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $employeeId = $this->getEmployeeId();
        
        // Only check employee_id for non-admin users (group_id != 1 and != 2)
        if (!$employeeId && !in_array($this->currentUser['group_id'], [1, 2])) {
            return redirect()->to('/dashboard')->with('error', 'Akun tidak terhubung dengan data pegawai');
        }

        $rules = [
            'leave_type_id' => 'required|numeric',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'reason' => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $totalDays = $this->leaveRequestModel->calculateTotalDays($startDate, $endDate);
        $leaveTypeId = $this->request->getPost('leave_type_id');

        // Check remaining balance
        $balances = $this->leaveRequestModel->getLeaveBalance($employeeId, date('Y', strtotime($startDate)));
        $targetBalance = null;
        foreach ($balances as $b) {
            if ($b['leave_type_id'] == $leaveTypeId) {
                $targetBalance = $b;
                break;
            }
        }

        // Only validate if quota is > 0
        if ($targetBalance && $targetBalance['quota'] > 0 && $totalDays > $targetBalance['remaining']) {
            return redirect()->back()->withInput()->with('error', "Sisa cuti tidak mencukupi untuk jenis cuti ini. Sisa tersedia: {$targetBalance['remaining']} hari.");
        }

        $data = [
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $this->request->getPost('reason'),
            'status' => 'pending',
        ];

        // Handle attachment
        $attachment = $this->request->getFile('attachment');
        if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
            $newName = $employeeId . '_' . time() . '_' . $attachment->getRandomName();
            $attachment->move(WRITEPATH . 'uploads/leave', $newName);
            $data['attachment'] = 'leave/' . $newName;
        }

        $this->leaveRequestModel->insert($data);

        return redirect()->to('/leave')->with('success', 'Pengajuan cuti berhasil dikirim');
    }

    public function approval()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $status = $this->request->getGet('status') ?? 'pending';
        
        $builder = $this->leaveRequestModel->select('
            leave_requests.*,
            employees.full_name as employee_name,
            employees.employee_code,
            departments.name as department_name,
            leave_types.name as leave_type_name
        ')
            ->join('employees', 'employees.id = leave_requests.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.status', $status);

        $departmentId = $this->request->getGet('department_id');
        if ($departmentId) {
            $builder->where('employees.department_id', $departmentId);
        }

        // Filter: Admin (1) and HR (2) can see all, others only see their subordinates
        if (!in_array($this->currentUser['group_id'], [1, 2])) {
            $currentEmployeeId = $this->getEmployeeId();
            if ($currentEmployeeId) {
                $builder->where('employees.supervisor_id', $currentEmployeeId);
            } else {
                // If not an employee but not admin/hr, they see nothing
                $builder->where('1=0');
            }
        }

        $requests = $builder->orderBy('leave_requests.created_at', 'DESC')->findAll();

        $deptModel = new \App\Models\DepartmentModel();
        $departments = $deptModel->findAll();

        return view('leave/approval', $this->viewData([
            'title' => 'Persetujuan Cuti',
            'requests' => $requests,
            'status' => $status,
            'department_id' => $departmentId,
            'departments' => $departments,
        ]));
    }

    public function approve($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $request = $this->leaveRequestModel->find($id);
        
        if (!$request) {
            return $this->errorResponse('Data tidak ditemukan');
        }

        if ($request['status'] !== 'pending') {
            return $this->errorResponse('Status pengajuan sudah diproses');
        }

        // Permission check: Admin, HR, or direct supervisor
        $isAuthorized = in_array($this->currentUser['group_id'], [1, 2]);
        if (!$isAuthorized) {
            $employeeModel = new \App\Models\EmployeeModel();
            $targetEmployee = $employeeModel->find($request['employee_id']);
            if ($targetEmployee && $targetEmployee['supervisor_id'] == $this->getEmployeeId()) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return $this->errorResponse('Anda tidak memiliki wewenang untuk menyetujui pengajuan ini');
        }

        $this->leaveRequestModel->approve($id, $this->session->get('user_id'));

        // Create attendance records for leave days
        $attendanceModel = new AttendanceModel();
        $startDate = new \DateTime($request['start_date']);
        $endDate = new \DateTime($request['end_date']);
        $endDate->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($startDate, $interval, $endDate);

        $leaveType = $this->leaveTypeModel->find($request['leave_type_id']);
        $status = 'leave';
        
        if ($leaveType['code'] === 'SICK') {
            $status = 'sick';
        } elseif ($leaveType['code'] === 'PERMIT') {
            $status = 'permit';
        }

        foreach ($dateRange as $date) {
            // Check if attendance record exists
            $existing = $attendanceModel->where('employee_id', $request['employee_id'])
                ->where('date', $date->format('Y-m-d'))
                ->first();
            
            if (!$existing) {
                $attendanceModel->insert([
                    'employee_id' => $request['employee_id'],
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'notes' => 'Leave request #' . $id,
                ]);
            } else {
                $attendanceModel->update($existing['id'], [
                    'status' => $status,
                    'notes' => 'Leave request #' . $id,
                ]);
            }
        }

        return $this->successResponse(null, 'Pengajuan cuti disetujui');
    }

    public function reject($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $request = $this->leaveRequestModel->find($id);
        
        if (!$request) {
            return $this->errorResponse('Data tidak ditemukan');
        }

        if ($request['status'] !== 'pending') {
            return $this->errorResponse('Status pengajuan sudah diproses');
        }

        // Permission check: Admin, HR, or direct supervisor
        $isAuthorized = in_array($this->currentUser['group_id'], [1, 2]);
        if (!$isAuthorized) {
            $employeeModel = new \App\Models\EmployeeModel();
            $targetEmployee = $employeeModel->find($request['employee_id']);
            if ($targetEmployee && $targetEmployee['supervisor_id'] == $this->getEmployeeId()) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return $this->errorResponse('Anda tidak memiliki wewenang untuk memproses pengajuan ini');
        }

        $reason = $this->request->getPost('reason');
        
        if (!$reason) {
            return $this->errorResponse('Alasan penolakan wajib diisi');
        }

        $this->leaveRequestModel->reject($id, $this->session->get('user_id'), $reason);

        return $this->successResponse(null, 'Pengajuan cuti ditolak');
    }

    public function cancel($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $request = $this->leaveRequestModel->find($id);
        
        if (!$request) {
            return $this->errorResponse('Data tidak ditemukan');
        }

        $employeeId = $this->getEmployeeId();
        
        if ($request['employee_id'] != $employeeId) {
            return $this->errorResponse('Akses ditolak');
        }

        if ($request['status'] !== 'pending') {
            return $this->errorResponse('Tidak dapat membatalkan pengajuan yang sudah diproses');
        }

        $this->leaveRequestModel->update($id, ['status' => 'cancelled']);

        return $this->successResponse(null, 'Pengajuan cuti dibatalkan');
    }

    public function history()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $year = $this->request->getGet('year') ?? date('Y');
        
        $requests = $this->leaveRequestModel->select('
            leave_requests.*,
            employees.full_name as employee_name,
            employees.employee_code,
            departments.name as department_name,
            leave_types.name as leave_type_name
        ')
            ->join('employees', 'employees.id = leave_requests.employee_id')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('YEAR(leave_requests.start_date)', $year)
            ->orderBy('leave_requests.created_at', 'DESC')
            ->findAll();

        return view('leave/history', $this->viewData([
            'title' => 'Riwayat Cuti',
            'requests' => $requests,
            'year' => $year,
        ]));
    }
}
