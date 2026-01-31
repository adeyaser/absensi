<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Laporan Absensi</h1>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Departemen</label>
                <select name="department_id" class="form-select">
                    <option value="">Semua Departemen</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= ($department_id ?? '') == $dept['id'] ? 'selected' : '' ?>>
                        <?= esc($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="<?= current_url() ?>?<?= http_build_query($_GET) ?>&export=excel" class="btn btn-success w-100">
                    <i class="fas fa-file-excel me-2"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?= $summary['total_employees'] ?? 0 ?></h4>
                <small>Total Karyawan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?= number_format(($summary['avg_attendance'] ?? 0), 1) ?>%</h4>
                <small>Rata-rata Kehadiran</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4><?= $summary['total_late'] ?? 0 ?></h4>
                <small>Total Keterlambatan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?= $summary['total_overtime'] ?? 0 ?> jam</h4>
                <small>Total Lembur</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-table me-2"></i>
            Rekap Absensi <?= date('F Y', mktime(0, 0, 0, $month ?? date('n'), 1, $year ?? date('Y'))) ?>
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm" id="reportTable">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-center" style="min-width: 200px">Karyawan</th>
                        <th rowspan="2" class="align-middle text-center">Dept</th>
                        <?php 
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month ?? date('n'), $year ?? date('Y'));
                        for ($d = 1; $d <= $daysInMonth; $d++): 
                            $date = mktime(0, 0, 0, $month ?? date('n'), $d, $year ?? date('Y'));
                            $dayName = date('D', $date);
                            $isWeekend = in_array(date('w', $date), [0, 6]);
                        ?>
                        <th class="text-center <?= $isWeekend ? 'bg-light' : '' ?>" style="min-width: 30px">
                            <small><?= $d ?></small>
                        </th>
                        <?php endfor; ?>
                        <th rowspan="2" class="align-middle text-center">H</th>
                        <th rowspan="2" class="align-middle text-center">A</th>
                        <th rowspan="2" class="align-middle text-center">T</th>
                        <th rowspan="2" class="align-middle text-center">C</th>
                        <th rowspan="2" class="align-middle text-center">%</th>
                    </tr>
                    <tr>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                            $date = mktime(0, 0, 0, $month ?? date('n'), $d, $year ?? date('Y'));
                            $dayName = substr(date('D', $date), 0, 1);
                            $isWeekend = in_array(date('w', $date), [0, 6]);
                        ?>
                        <th class="text-center <?= $isWeekend ? 'bg-light' : '' ?>">
                            <small class="text-muted"><?= $dayName ?></small>
                        </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($report)): ?>
                    <?php foreach ($report as $row): ?>
                    <tr>
                        <td>
                            <strong><?= esc($row['employee_name']) ?></strong>
                            <br><small class="text-muted"><?= esc($row['employee_id']) ?></small>
                        </td>
                        <td class="text-center"><small><?= esc($row['department_code'] ?? '-') ?></small></td>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                            $status = $row['days'][$d] ?? null;
                            $statusClass = [
                                'H' => 'bg-success text-white',
                                'T' => 'bg-warning',
                                'A' => 'bg-danger text-white',
                                'C' => 'bg-info text-white',
                                'L' => 'bg-secondary text-white',
                                'W' => 'bg-light',
                            ];
                        ?>
                        <td class="text-center <?= $statusClass[$status] ?? '' ?>" style="font-size: 10px;">
                            <?= $status ?? '' ?>
                        </td>
                        <?php endfor; ?>
                        <td class="text-center"><strong class="text-success"><?= $row['present'] ?? 0 ?></strong></td>
                        <td class="text-center"><strong class="text-danger"><?= $row['absent'] ?? 0 ?></strong></td>
                        <td class="text-center"><strong class="text-warning"><?= $row['late'] ?? 0 ?></strong></td>
                        <td class="text-center"><strong class="text-info"><?= $row['leave'] ?? 0 ?></strong></td>
                        <td class="text-center">
                            <strong><?= number_format($row['attendance_rate'] ?? 0, 0) ?>%</strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="<?= $daysInMonth + 7 ?>" class="text-center text-muted">
                            Tidak ada data untuk periode ini
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                <strong>Keterangan:</strong>
                <span class="badge bg-success">H</span> Hadir
                <span class="badge bg-warning text-dark">T</span> Terlambat
                <span class="badge bg-danger">A</span> Absen
                <span class="badge bg-info">C</span> Cuti
                <span class="badge bg-secondary">L</span> Libur
                <span class="badge bg-light text-dark">W</span> Weekend
            </small>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #reportTable th, #reportTable td {
        padding: 4px 6px;
        vertical-align: middle;
    }
</style>
<?= $this->endSection() ?>
