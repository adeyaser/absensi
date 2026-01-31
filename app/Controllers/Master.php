<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\PositionModel;
use App\Models\WorkScheduleModel;
use App\Models\OfficeLocationModel;
use App\Models\HolidayModel;
use App\Models\LeaveTypeModel;
use App\Models\SalaryComponentModel;
use App\Models\PositionSalaryComponentModel;

class Master extends BaseController
{
    public function departments()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new DepartmentModel();
        $data = $model->getWithEmployeeCount();

        return view('master/departments', $this->viewData([
            'title' => 'Departemen',
            'departments' => $data,
        ]));
    }

    public function saveDepartment()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new DepartmentModel();
        $id = $this->request->getPost('id');

        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $model->update($id, $data);
            $message = 'Departemen berhasil diupdate';
        } else {
            $model->insert($data);
            $message = 'Departemen berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteDepartment($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new DepartmentModel();
        $model->delete($id);

        return $this->successResponse(null, 'Departemen berhasil dihapus');
    }

    public function positions()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new PositionModel();
        $departmentModel = new DepartmentModel();
        $compModel = new SalaryComponentModel();
        $posCompModel = new PositionSalaryComponentModel();
        
        $data = $model->getWithDepartment();
        $departments = $departmentModel->getActive();
        
        $earningComponents = $compModel->getEarnings();
        $deductionComponents = $compModel->getDeductions();

        // Attach components to each position
        foreach ($data as &$pos) {
            $pos['salary_components'] = $posCompModel->getByPosition($pos['id']);
        }

        return view('master/positions', $this->viewData([
            'title' => 'Jabatan',
            'positions' => $data,
            'departments' => $departments,
            'earningComponents' => $earningComponents,
            'deductionComponents' => $deductionComponents,
        ]));
    }

    public function createPosition()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $departmentModel = new DepartmentModel();
        $compModel = new SalaryComponentModel();

        return view('master/position_form', $this->viewData([
            'title' => 'Tambah Jabatan',
            'departments' => $departmentModel->getActive(),
            'earningComponents' => $compModel->getEarnings(),
            'deductionComponents' => $compModel->getDeductions(),
            'position' => null
        ]));
    }

    public function editPosition($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new PositionModel();
        $departmentModel = new DepartmentModel();
        $compModel = new SalaryComponentModel();
        $posCompModel = new PositionSalaryComponentModel();

        $position = $model->find($id);
        if (!$position) {
            return redirect()->to('/master/positions')->with('error', 'Jabatan tidak ditemukan');
        }

        $position['salary_components'] = $posCompModel->getByPosition($id);

        return view('master/position_form', $this->viewData([
            'title' => 'Edit Jabatan',
            'departments' => $departmentModel->getActive(),
            'earningComponents' => $compModel->getEarnings(),
            'deductionComponents' => $compModel->getDeductions(),
            'position' => $position
        ]));
    }

    public function savePosition($id = null)
    {
        if (!$this->isLoggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->errorResponse('Unauthorized', 401);
            }
            return redirect()->to('/auth');
        }

        $model = new PositionModel();
        $posCompModel = new PositionSalaryComponentModel();
        $id = $id ?? $this->request->getPost('id');
        $components = $this->request->getPost('components') ?? [];

        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'department_id' => $this->request->getPost('department_id'),
            'level' => $this->request->getPost('level') ?? 1,
            'base_salary' => $this->request->getPost('base_salary') ?? 0,
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($id) {
                $model->update($id, $data);
                $message = 'Jabatan berhasil diupdate';
            } else {
                $id = $model->insert($data);
                $message = 'Jabatan berhasil ditambahkan';
            }

            // Save position salary components
            $posCompModel->deleteByPosition($id);
            foreach ($components as $compId => $compData) {
                if (isset($compData['enabled']) && $compData['enabled'] == 1) {
                    $posCompModel->insert([
                        'position_id' => $id,
                        'component_id' => $compId,
                        'amount' => $compData['amount'] ?? 0,
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                if ($this->request->isAJAX()) {
                    return $this->errorResponse('Gagal menyimpan data jabatan');
                }
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data jabatan');
            }

            if ($this->request->isAJAX()) {
                return $this->successResponse(null, $message);
            }
            return redirect()->to('/master/positions')->with('success', $message);

        } catch (\Exception $e) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->errorResponse('Terjadi kesalahan: ' . $e->getMessage());
            }
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deletePosition($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new PositionModel();
        $model->delete($id);

        return $this->successResponse(null, 'Jabatan berhasil dihapus');
    }

    public function schedules()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new WorkScheduleModel();
        $data = $model->findAll();

        return view('master/schedules', $this->viewData([
            'title' => 'Jadwal Kerja',
            'schedules' => $data,
        ]));
    }

    public function saveSchedule()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new WorkScheduleModel();
        $id = $this->request->getPost('id');

        $workDays = $this->request->getPost('working_days');
        if (is_array($workDays)) {
            $workDays = implode(',', $workDays);
        }

        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'clock_in' => $this->request->getPost('clock_in'),
            'clock_out' => $this->request->getPost('clock_out'),
            'break_start' => $this->request->getPost('break_start'),
            'break_end' => $this->request->getPost('break_end'),
            'late_tolerance' => $this->request->getPost('late_tolerance') ?? 15,
            'early_leave_tolerance' => $this->request->getPost('early_leave_tolerance') ?? 0,
            'work_hours' => $this->request->getPost('work_hours') ?? 8,
            'work_days' => $workDays ?? '1,2,3,4,5',
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $model->update($id, $data);
            $message = 'Jadwal kerja berhasil diupdate';
        } else {
            $model->insert($data);
            $message = 'Jadwal kerja berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteSchedule($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new WorkScheduleModel();
        $model->delete($id);

        return $this->successResponse(null, 'Jadwal kerja berhasil dihapus');
    }

    public function locations()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new OfficeLocationModel();
        $data = $model->findAll();

        return view('master/locations', $this->viewData([
            'title' => 'Lokasi Kantor',
            'locations' => $data,
        ]));
    }

    public function saveLocation()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new OfficeLocationModel();
        $id = $this->request->getPost('id');

        $data = [
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'radius' => $this->request->getPost('radius') ?? 100,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $model->update($id, $data);
            $message = 'Lokasi berhasil diupdate';
        } else {
            $model->insert($data);
            $message = 'Lokasi berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteLocation($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new OfficeLocationModel();
        $model->delete($id);

        return $this->successResponse(null, 'Lokasi berhasil dihapus');
    }

    public function holidays()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new HolidayModel();
        $year = $this->request->getGet('year') ?? date('Y');
        $data = $model->getByYear($year);

        return view('master/holidays', $this->viewData([
            'title' => 'Hari Libur',
            'holidays' => $data,
            'year' => $year,
        ]));
    }

    public function saveHoliday()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new HolidayModel();
        $id = $this->request->getPost('id');

        $data = [
            'date' => $this->request->getPost('date'),
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_national' => $this->request->getPost('is_national') ? 1 : 0,
        ];

        if ($id) {
            $model->update($id, $data);
            $message = 'Hari libur berhasil diupdate';
        } else {
            $model->insert($data);
            $message = 'Hari libur berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteHoliday($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new HolidayModel();
        $model->delete($id);

        return $this->successResponse(null, 'Hari libur berhasil dihapus');
    }

    public function syncHolidays()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $year = date('Y');
        // Using external API for Indonesian holidays
        $baseUrl = getenv('API_HOLIDAY_URL') ?: 'https://dayoffapi.vercel.app/api';
        $url = $baseUrl . "?year=" . $year;
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $url, [
                'verify' => false, // Bypass SSL if needed in dev
                'timeout' => 10
            ]);
            
            $body = $response->getBody();
            $holidays = json_decode($body, true);
            
            if (empty($holidays) || !is_array($holidays)) {
                 return $this->errorResponse('Gagal mengambil data dari server eksternal.', 500);
            }

            $model = new HolidayModel();
            $count = 0;

            foreach ($holidays as $h) {
                // Ensure date format is YYYY-MM-DD
                $date = $h['tanggal']; // DayOffAPI uses 'tanggal'
                
                // Check if exists
                if (!$model->where('date', $date)->first()) {
                    $model->insert([
                        'date' => $date,
                        'name' => $h['keterangan'], // DayOffAPI uses 'keterangan'
                        'description' => 'Sinkronisasi Otomatis',
                        'is_national' => ($h['is_cuti'] ?? true) ? 1 : 0
                    ]);
                    $count++;
                }
            }

            return $this->successResponse(null, "$count hari libur baru berhasil ditambahkan untuk tahun $year.");
            
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function leaveTypes()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth');
        }

        $model = new LeaveTypeModel();
        $data = $model->findAll();

        return view('master/leave_types', $this->viewData([
            'title' => 'Jenis Cuti',
            'leaveTypes' => $data,
        ]));
    }

    public function saveLeaveType()
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new LeaveTypeModel();
        $id = $this->request->getPost('id');

        $data = [
            'code' => $this->request->getPost('code'),
            'name' => $this->request->getPost('name'),
            'quota' => $this->request->getPost('quota') ?? 0,
            'is_paid' => $this->request->getPost('is_paid') ? 1 : 0,
            'is_deductible' => $this->request->getPost('is_deductible') ? 1 : 0,
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $model->update($id, $data);
            $message = 'Jenis cuti berhasil diupdate';
        } else {
            $model->insert($data);
            $message = 'Jenis cuti berhasil ditambahkan';
        }

        return $this->successResponse(null, $message);
    }

    public function deleteLeaveType($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $model = new LeaveTypeModel();
        $model->delete($id);

        return $this->successResponse(null, 'Jenis cuti berhasil dihapus');
    }
}
