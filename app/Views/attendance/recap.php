<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Rekap Absensi</h1>
    <a href="<?= base_url('attendance/export') ?>?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-success">
        <i class="fas fa-file-excel me-2"></i> Export Excel
    </a>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $month == $i ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select">
                    <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                    <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Departemen</label>
                <select name="department_id" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>><?= esc($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row mb-4">
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= $summary['total_employees'] ?? 0 ?></h3>
                <small>Total Pegawai</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?= $summary['present'] ?? 0 ?></h3>
                <small>Total Hadir</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3><?= $summary['late'] ?? 0 ?></h3>
                <small>Total Terlambat</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3><?= $summary['absent'] ?? 0 ?></h3>
                <small>Total Tidak Hadir</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?= $summary['leave'] ?? 0 ?></h3>
                <small>Total Cuti</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-4 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h3><?= $summary['sick'] ?? 0 ?></h3>
                <small>Total Sakit</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="recapTable">
                <thead>
                    <tr>
                        <th class="sticky-col">Pegawai</th>
                        <th>Dept</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <?php 
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $dayName = date('D', strtotime($date));
                        $isWeekend = in_array($dayName, ['Sat', 'Sun']);
                        ?>
                        <th class="text-center <?= $isWeekend ? 'bg-light' : '' ?>" style="min-width: 35px;">
                            <?= $d ?>
                            <br><small><?= substr($dayName, 0, 1) ?></small>
                        </th>
                        <?php endfor; ?>
                        <th class="text-center">H</th>
                        <th class="text-center">T</th>
                        <th class="text-center">A</th>
                        <th class="text-center">C</th>
                        <th class="text-center">S</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recaps)): ?>
                    <?php foreach ($recaps as $emp): ?>
                    <tr>
                        <td class="sticky-col">
                            <strong><?= esc($emp['employee_code']) ?></strong>
                            <br><small><?= esc($emp['full_name']) ?></small>
                        </td>
                        <td><small><?= esc($emp['department_code'] ?? '-') ?></small></td>
                        <?php 
                        $counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'leave' => 0, 'sick' => 0];
                        for ($d = 1; $d <= $daysInMonth; $d++): 
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $att = $emp['attendances'][$date] ?? null;
                        $dayName = date('D', strtotime($date));
                        $isWeekend = in_array($dayName, ['Sat', 'Sun']);
                        ?>
                        <td class="text-center <?= $isWeekend ? 'bg-light' : '' ?>">
                            <?php if ($att): ?>
                                <?php 
                                if ($att['status'] == 'present') { echo '<span class="text-success">✓</span>'; $counts['present']++; }
                                elseif ($att['status'] == 'late') { echo '<span class="text-warning">L</span>'; $counts['late']++; }
                                elseif ($att['status'] == 'absent') { echo '<span class="text-danger">X</span>'; $counts['absent']++; }
                                elseif ($att['status'] == 'leave') { echo '<span class="text-info">C</span>'; $counts['leave']++; }
                                elseif ($att['status'] == 'sick') { echo '<span class="text-secondary">S</span>'; $counts['sick']++; }
                                else { echo '-'; }
                                ?>
                            <?php elseif (!$isWeekend && strtotime($date) <= strtotime('today')): ?>
                                <span class="text-danger">X</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                        <td class="text-center"><span class="badge bg-success"><?= $counts['present'] ?></span></td>
                        <td class="text-center"><span class="badge bg-warning"><?= $counts['late'] ?></span></td>
                        <td class="text-center"><span class="badge bg-danger"><?= $counts['absent'] ?></span></td>
                        <td class="text-center"><span class="badge bg-info"><?= $counts['leave'] ?></span></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $counts['sick'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="<?= $daysInMonth + 7 ?>" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted">
        <strong>Keterangan:</strong>
        <span class="text-success ms-3">✓ Hadir</span>
        <span class="text-warning ms-3">L Terlambat</span>
        <span class="text-danger ms-3">X Tidak Hadir</span>
        <span class="text-info ms-3">C Cuti</span>
        <span class="text-secondary ms-3">S Sakit</span>
    </small>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .sticky-col {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 1;
    }
    
    #recapTable th, #recapTable td {
        white-space: nowrap;
        padding: 0.35rem;
        font-size: 0.85rem;
    }
</style>
<?= $this->endSection() ?>
