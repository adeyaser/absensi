<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Profil Saya</h1>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="<?= base_url('uploads/photos/' . ($employee['photo'] ?? 'default.png')) ?>" 
                     alt="Profile" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h4><?= esc($employee['full_name'] ?? session('name')) ?></h4>
                <p class="text-muted mb-1"><?= esc($employee['position_name'] ?? '-') ?></p>
                <p class="text-muted"><?= esc($employee['department_name'] ?? '-') ?></p>
                
                <?php if ($employee): ?>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <span class="badge bg-<?= $employee['employment_status'] === 'active' ? 'success' : 'warning' ?>">
                        <?= ucfirst($employee['employment_status'] ?? 'N/A') ?>
                    </span>
                    <span class="badge bg-primary"><?= esc($employee['employee_id']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($employee): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar me-2"></i> Informasi Cuti
            </div>
            <div class="card-body">
                <?php foreach ($leaveBalances ?? [] as $balance): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><?= esc($balance['leave_name']) ?></span>
                    <span class="badge bg-primary"><?= $balance['remaining'] ?>/<?= $balance['quota'] ?> hari</span>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($leaveBalances)): ?>
                <p class="text-muted text-center mb-0">Belum ada data cuti</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#personal">
                            <i class="fas fa-user me-2"></i> Data Pribadi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#account">
                            <i class="fas fa-key me-2"></i> Akun
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal">
                        <?php if ($employee): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nama Lengkap</label>
                                <p class="mb-0"><strong><?= esc($employee['full_name']) ?></strong></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">NIK</label>
                                <p class="mb-0"><?= esc($employee['nik'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="mb-0"><?= esc($employee['email'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Telepon</label>
                                <p class="mb-0"><?= esc($employee['phone'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Jenis Kelamin</label>
                                <p class="mb-0"><?= $employee['gender'] === 'male' ? 'Laki-laki' : 'Perempuan' ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tanggal Lahir</label>
                                <p class="mb-0"><?= $employee['birth_date'] ? date('d F Y', strtotime($employee['birth_date'])) : '-' ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tempat Lahir</label>
                                <p class="mb-0"><?= esc($employee['birth_place'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Agama</label>
                                <p class="mb-0"><?= esc(ucfirst($employee['religion'] ?? '-')) ?></p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted">Alamat</label>
                                <p class="mb-0"><?= esc($employee['address'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tanggal Bergabung</label>
                                <p class="mb-0"><?= $employee['join_date'] ? date('d F Y', strtotime($employee['join_date'])) : '-' ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tipe Kontrak</label>
                                <p class="mb-0"><?= ucfirst($employee['contract_type'] ?? '-') ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-user-slash fa-3x mb-3"></i>
                            <p>Data karyawan belum terhubung dengan akun Anda.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Account Tab -->
                    <div class="tab-pane fade" id="account">
                        <form action="<?= base_url('profile/update') ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= esc(session('username')) ?>" disabled>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= esc(session('email')) ?>">
                            </div>
                            
                            <hr>
                            <h6>Ubah Password</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="new_password" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" name="confirm_password" class="form-control">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($employee): ?>
        <!-- Recent Attendance -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i> Absensi Terakhir</span>
                <a href="<?= base_url('attendance/history') ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAttendance ?? [] as $att): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($att['date'])) ?></td>
                            <td><?= $att['clock_in'] ? date('H:i', strtotime($att['clock_in'])) : '-' ?></td>
                            <td><?= $att['clock_out'] ? date('H:i', strtotime($att['clock_out'])) : '-' ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'present' => 'success',
                                    'late' => 'warning',
                                    'absent' => 'danger',
                                    'leave' => 'info'
                                ];
                                $statusText = [
                                    'present' => 'Hadir',
                                    'late' => 'Terlambat',
                                    'absent' => 'Absen',
                                    'leave' => 'Cuti'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusClass[$att['status']] ?? 'secondary' ?>">
                                    <?= $statusText[$att['status']] ?? $att['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentAttendance)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data absensi</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
