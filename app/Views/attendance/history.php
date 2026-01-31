<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Riwayat Absensi</h1>
    <form class="d-flex gap-2">
        <select name="month" class="form-select" style="width: auto;">
            <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $month == $i ? 'selected' : '' ?>>
                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
            </option>
            <?php endfor; ?>
        </select>
        <select name="year" class="form-select" style="width: auto;">
            <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
            <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>

<!-- Recap Summary -->
<?php if ($recap): ?>
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['present'] ?? 0 ?></h2>
                <small>Hadir</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['late'] ?? 0 ?></h2>
                <small>Terlambat</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['absent'] ?? 0 ?></h2>
                <small>Tidak Hadir</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['leave'] ?? 0 ?></h2>
                <small>Cuti</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['sick'] ?? 0 ?></h2>
                <small>Sakit</small>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $recap['total_work_hours'] ?? 0 ?></h2>
                <small>Jam Kerja</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendances)): ?>
                    <?php foreach ($attendances as $att): ?>
                    <tr>
                        <td>
                            <strong><?= date('d M Y', strtotime($att['date'])) ?></strong>
                            <br><small class="text-muted"><?= date('l', strtotime($att['date'])) ?></small>
                        </td>
                        <td>
                            <?php if ($att['clock_in']): ?>
                            <span class="text-success">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                <?= date('H:i', strtotime($att['clock_in'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($att['clock_out']): ?>
                            <span class="text-danger">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                <?= date('H:i', strtotime($att['clock_out'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($att['work_hours']): ?>
                            <?= $att['work_hours'] ?> jam
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = [
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'leave' => 'info',
                                'sick' => 'secondary',
                                'early_leave' => 'warning',
                                'permit' => 'info'
                            ];
                            $statusText = [
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'leave' => 'Cuti',
                                'sick' => 'Sakit',
                                'early_leave' => 'Pulang Cepat',
                                'permit' => 'Izin'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusClass[$att['status']] ?? 'secondary' ?>">
                                <?= $statusText[$att['status']] ?? $att['status'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($att['late_minutes'] > 0): ?>
                            <small class="text-warning">Terlambat <?= $att['late_minutes'] ?> menit</small>
                            <?php endif; ?>
                            <?php if ($att['early_leave_minutes'] > 0): ?>
                            <small class="text-warning">Pulang cepat <?= $att['early_leave_minutes'] ?> menit</small>
                            <?php endif; ?>
                            <?php if (!$att['is_valid_location']): ?>
                            <br><small class="text-danger"><i class="fas fa-map-marker-alt"></i> Di luar area</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data absensi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
