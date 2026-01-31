<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Detail Pegawai</h1>
    <div>
        <a href="<?= base_url('employees/edit/' . $employee['id']) ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i> Edit
        </a>
        <a href="<?= base_url('employees') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <?php if ($employee['photo']): ?>
                <img src="<?= base_url('writable/uploads/' . $employee['photo']) ?>" alt="Photo" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                <?php else: ?>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px; font-size: 4rem;">
                    <?= strtoupper(substr($employee['full_name'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                
                <h4><?= esc($employee['full_name']) ?></h4>
                <p class="text-muted mb-1"><?= esc($employee['position_name'] ?? '-') ?></p>
                <p class="text-muted"><?= esc($employee['department_name'] ?? '-') ?></p>
                
                <span class="badge bg-<?= $employee['is_active'] ? 'success' : 'danger' ?> fs-6">
                    <?= $employee['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                </span>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Statistik Bulan Ini
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Hari Hadir</span>
                        <span class="badge bg-success"><?= $stats['present_days'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Hari Terlambat</span>
                        <span class="badge bg-warning"><?= $stats['late_days'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Tidak Hadir</span>
                        <span class="badge bg-danger"><?= $stats['absent_days'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Jam Kerja</span>
                        <span class="badge bg-primary"><?= $stats['total_hours'] ?? 0 ?> jam</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#personal">Data Pribadi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#employment">Kepegawaian</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#salary">Komponen Gaji</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#attendance">Riwayat Absensi</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Personal Data -->
            <div class="tab-pane fade show active" id="personal">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="200">Kode Pegawai</td>
                                <td>: <strong><?= esc($employee['employee_code']) ?></strong></td>
                            </tr>
                            <tr>
                                <td>NIK</td>
                                <td>: <?= esc($employee['nik'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Jenis Kelamin</td>
                                <td>: <?= $employee['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            </tr>
                            <tr>
                                <td>Tempat, Tanggal Lahir</td>
                                <td>: <?= esc($employee['birth_place'] ?? '-') ?>, <?= $employee['birth_date'] ? date('d F Y', strtotime($employee['birth_date'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <td>Agama</td>
                                <td>: <?= esc($employee['religion'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Status Pernikahan</td>
                                <td>: <?= $employee['marital_status'] == 'single' ? 'Belum Menikah' : ($employee['marital_status'] == 'married' ? 'Menikah' : 'Cerai') ?></td>
                            </tr>
                            <tr>
                                <td>Telepon</td>
                                <td>: <?= esc($employee['phone'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>: <?= esc($employee['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td>: <?= esc($employee['address'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Employment Data -->
            <div class="tab-pane fade" id="employment">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="200">Departemen</td>
                                <td>: <?= esc($employee['department_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Jabatan</td>
                                <td>: <?= esc($employee['position_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Status Kepegawaian</td>
                                <td>: 
                                    <?php
                                    $statusMap = [
                                        'permanent' => 'Tetap',
                                        'contract' => 'Kontrak',
                                        'internship' => 'Magang',
                                        'probation' => 'Percobaan'
                                    ];
                                    echo $statusMap[$employee['employment_status']] ?? '-';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tanggal Masuk</td>
                                <td>: <?= $employee['join_date'] ? date('d F Y', strtotime($employee['join_date'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <td>Masa Kerja</td>
                                <td>: 
                                    <?php
                                    if ($employee['join_date']) {
                                        $joinDate = new DateTime($employee['join_date']);
                                        $now = new DateTime();
                                        $interval = $joinDate->diff($now);
                                        echo $interval->y . ' tahun ' . $interval->m . ' bulan';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>NPWP</td>
                                <td>: <?= esc($employee['npwp'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>BPJS Kesehatan</td>
                                <td>: <?= esc($employee['bpjs_kesehatan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>BPJS Ketenagakerjaan</td>
                                <td>: <?= esc($employee['bpjs_ketenagakerjaan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>Bank</td>
                                <td>: <?= esc($employee['bank_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td>No. Rekening</td>
                                <td>: <?= esc($employee['bank_account'] ?? '-') ?> (<?= esc($employee['bank_holder'] ?? '-') ?>)</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Salary Components -->
            <div class="tab-pane fade" id="salary">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Komponen Gaji</span>
                        <button class="btn btn-sm btn-primary" onclick="manageSalaryComponents()">
                            <i class="fas fa-cog me-1"></i> Kelola
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Tipe</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Gaji Pokok</strong></td>
                                    <td><span class="badge bg-success">Pendapatan</span></td>
                                    <td class="text-end">Rp <?= number_format($salary['base_salary'] ?? 0, 0, ',', '.') ?></td>
                                </tr>
                                <?php if (!empty($salaryComponents)): ?>
                                <?php foreach ($salaryComponents as $comp): ?>
                                <tr>
                                    <td><?= esc($comp['component_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $comp['type'] == 'earning' ? 'success' : 'danger' ?>">
                                            <?= $comp['type'] == 'earning' ? 'Pendapatan' : 'Potongan' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?= $comp['type'] == 'earning' ? '+' : '-' ?>Rp <?= number_format($comp['amount'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="2">Estimasi Gaji Bersih</th>
                                    <th class="text-end">Rp <?= number_format($salary['estimated_net'] ?? $salary['base_salary'] ?? 0, 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Attendance History -->
            <div class="tab-pane fade" id="attendance">
                <div class="card">
                    <div class="card-header">
                        Riwayat Absensi (10 Terakhir)
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentAttendances)): ?>
                                <?php foreach ($recentAttendances as $att): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($att['date'])) ?></td>
                                    <td><?= $att['clock_in'] ? date('H:i', strtotime($att['clock_in'])) : '-' ?></td>
                                    <td><?= $att['clock_out'] ? date('H:i', strtotime($att['clock_out'])) : '-' ?></td>
                                    <td><?= $att['work_hours'] ?? '-' ?></td>
                                    <td>
                                        <?php
                                        $statusClass = ['present' => 'success', 'late' => 'warning', 'absent' => 'danger', 'leave' => 'info', 'sick' => 'secondary'];
                                        $statusText = ['present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'leave' => 'Cuti', 'sick' => 'Sakit'];
                                        ?>
                                        <span class="badge bg-<?= $statusClass[$att['status']] ?? 'secondary' ?>">
                                            <?= $statusText[$att['status']] ?? $att['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data absensi</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function manageSalaryComponents() {
        // Redirect to the new standalone employee salary edit page
        window.location.href = '<?= base_url('payroll/employee-salary/edit/' . $employee['id']) ?>';
    }
</script>
<?= $this->endSection() ?>
