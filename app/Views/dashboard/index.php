<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="row">
    <?php if (isset($currentUser) && in_array($currentUser['group_id'], [1, 2, 3, 4])): ?>
    <!-- Admin/Manager Stats -->
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card primary">
            <div class="stat-content">
                <h3><?= $stats['total_employees'] ?? 0 ?></h3>
                <p>Total Pegawai</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card success">
            <div class="stat-content">
                <h3><?= $stats['present_today'] ?? 0 ?></h3>
                <p>Hadir Hari Ini</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card danger">
            <div class="stat-content">
                <h3><?= $stats['absent_today'] ?? 0 ?></h3>
                <p>Tidak Hadir</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card warning">
            <div class="stat-content">
                <h3><?= $stats['pending_leaves'] ?? 0 ?></h3>
                <p>Pengajuan Cuti</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-calendar-minus"></i>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Employee Stats -->
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card success">
            <div class="stat-content">
                <h3><?= $stats['present_days'] ?? 0 ?></h3>
                <p>Hari Hadir</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card warning">
            <div class="stat-content">
                <h3><?= $stats['late_days'] ?? 0 ?></h3>
                <p>Hari Terlambat</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card info">
            <div class="stat-content">
                <h3><?= $stats['leave_days'] ?? 0 ?></h3>
                <p>Hari Cuti</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-calendar-minus"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card primary">
            <div class="stat-content">
                <h3><?= $stats['leave_balance']['remaining'] ?? 12 ?></h3>
                <p>Sisa Cuti</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-umbrella-beach"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Today's Attendance -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Absensi Hari Ini</span>
                <span class="text-muted"><?= date('d F Y') ?></span>
            </div>
            <div class="card-body">
                <?php if (isset($myAttendance)): ?>
                    <?php if ($myAttendance): ?>
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded mb-3 mb-md-0">
                                <h5 class="text-muted mb-3">Jam Masuk</h5>
                                <h2 class="text-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <?= date('H:i', strtotime($myAttendance['clock_in'])) ?>
                                </h2>
                                <?php if ($myAttendance['status'] === 'late'): ?>
                                <span class="badge bg-warning">Terlambat <?= $myAttendance['late_minutes'] ?> menit</span>
                                <?php else: ?>
                                <span class="badge bg-success">Tepat Waktu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h5 class="text-muted mb-3">Jam Pulang</h5>
                                <?php if ($myAttendance['clock_out']): ?>
                                <h2 class="text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    <?= date('H:i', strtotime($myAttendance['clock_out'])) ?>
                                </h2>
                                <span class="badge bg-info">Durasi: <?= $myAttendance['work_hours'] ?? '0' ?> jam</span>
                                <?php else: ?>
                                <h2 class="text-muted">--:--</h2>
                                <span class="badge bg-secondary">Belum absen pulang</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                        <h4>Anda belum absen hari ini</h4>
                        <p class="text-muted">Silakan lakukan absen masuk</p>
                        <a href="<?= base_url('attendance/clock') ?>" class="btn btn-primary">
                            <i class="fas fa-fingerprint me-2"></i> Absen Sekarang
                        </a>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">Tidak ada data absensi</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($todayAttendance) && !empty($todayAttendance)): ?>
        <!-- Today's Attendance List (for admin) -->
        <div class="card">
            <div class="card-header">
                <span>Daftar Kehadiran Hari Ini</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pegawai</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($todayAttendance, 0, 10) as $att): ?>
                            <tr>
                                <td><?= esc($att['full_name']) ?></td>
                                <td><?= $att['clock_in'] ? date('H:i', strtotime($att['clock_in'])) : '-' ?></td>
                                <td><?= $att['clock_out'] ? date('H:i', strtotime($att['clock_out'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'absent' => 'danger',
                                        'leave' => 'info',
                                        'sick' => 'secondary'
                                    ];
                                    $statusText = [
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'absent' => 'Tidak Hadir',
                                        'leave' => 'Cuti',
                                        'sick' => 'Sakit'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClass[$att['status']] ?? 'secondary' ?>">
                                        <?= $statusText[$att['status']] ?? $att['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions & Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <span>Aksi Cepat</span>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('attendance/clock') ?>" class="btn btn-primary">
                        <i class="fas fa-fingerprint me-2"></i> Absen Masuk/Pulang
                    </a>
                    <a href="<?= base_url('leave/create') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-plus me-2"></i> Ajukan Cuti
                    </a>
                    <a href="<?= base_url('attendance/history') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i> Riwayat Absensi
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Server Time -->
        <div class="card">
            <div class="card-header">
                <span>Waktu Server</span>
            </div>
            <div class="card-body text-center">
                <h1 id="serverTime" class="display-4 text-primary mb-0"><?= date('H:i:s') ?></h1>
                <p class="text-muted"><?= date('l, d F Y') ?></p>
            </div>
        </div>
        
        <!-- Info -->
        <div class="card">
            <div class="card-header">
                <span>Informasi</span>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-building text-primary me-2"></i>
                        <strong>Perusahaan:</strong> <?= $settings['company_name'] ?? 'PT. Contoh' ?>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong>Jam Kerja:</strong> <?= $settings['work_start_time'] ?? '08:00' ?> - <?= $settings['work_end_time'] ?? '17:00' ?>
                    </li>
                    <li>
                        <i class="fas fa-calendar text-primary me-2"></i>
                        <strong>Hari Kerja:</strong> Senin - Jumat
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Update server time every second
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('serverTime').textContent = `${hours}:${minutes}:${seconds}`;
    }
    
    setInterval(updateTime, 1000);
</script>
<?= $this->endSection() ?>
