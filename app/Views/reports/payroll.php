<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Laporan Penggajian</h1>
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
                <small>Karyawan Digaji</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>Rp <?= number_format(($summary['total_gross'] ?? 0), 0, ',', '.') ?></h4>
                <small>Total Gaji Kotor</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4>Rp <?= number_format(($summary['total_deductions'] ?? 0), 0, ',', '.') ?></h4>
                <small>Total Potongan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>Rp <?= number_format(($summary['total_net'] ?? 0), 0, ',', '.') ?></h4>
                <small>Total Gaji Bersih</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-table me-2"></i>
        Detail Penggajian <?= date('F Y', mktime(0, 0, 0, $month ?? date('n'), 1, $year ?? date('Y'))) ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="payrollReportTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th class="text-end">Gaji Pokok</th>
                        <th class="text-end">Tunjangan</th>
                        <th class="text-end">Lembur</th>
                        <th class="text-end">Gaji Kotor</th>
                        <th class="text-end">BPJS</th>
                        <th class="text-end">PPh21</th>
                        <th class="text-end">Lainnya</th>
                        <th class="text-end">Gaji Bersih</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($report)): ?>
                    <?php foreach ($report as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= esc($row['employee_name']) ?></strong>
                            <br><small class="text-muted"><?= esc($row['employee_id']) ?></small>
                        </td>
                        <td><?= esc($row['department'] ?? '-') ?></td>
                        <td class="text-end">Rp <?= number_format($row['base_salary'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end text-success">Rp <?= number_format($row['allowances'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end text-info">Rp <?= number_format($row['overtime'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end"><strong>Rp <?= number_format($row['gross_salary'] ?? 0, 0, ',', '.') ?></strong></td>
                        <td class="text-end text-danger">Rp <?= number_format($row['bpjs'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end text-danger">Rp <?= number_format($row['pph21'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end text-danger">Rp <?= number_format($row['other_deductions'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-end"><strong class="text-primary">Rp <?= number_format($row['net_salary'] ?? 0, 0, ',', '.') ?></strong></td>
                        <td>
                            <?php
                            $statusClass = ['draft' => 'secondary', 'approved' => 'info', 'paid' => 'success'];
                            $statusText = ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Dibayar'];
                            ?>
                            <span class="badge bg-<?= $statusClass[$row['status']] ?? 'secondary' ?>">
                                <?= $statusText[$row['status']] ?? $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted">Tidak ada data untuk periode ini</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($report)): ?>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTAL:</th>
                        <th class="text-end">Rp <?= number_format($totals['base_salary'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['allowances'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['overtime'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['gross_salary'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['bpjs'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['pph21'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['other_deductions'] ?? 0, 0, ',', '.') ?></th>
                        <th class="text-end">Rp <?= number_format($totals['net_salary'] ?? 0, 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i> Komposisi Gaji
            </div>
            <div class="card-body">
                <canvas id="compositionChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Gaji per Departemen
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function() {
        $('#payrollReportTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            paging: false,
            searching: false,
            info: false
        });
        
        // Composition Chart
        new Chart(document.getElementById('compositionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Gaji Pokok', 'Tunjangan', 'Lembur', 'Potongan'],
                datasets: [{
                    data: [
                        <?= $totals['base_salary'] ?? 0 ?>,
                        <?= $totals['allowances'] ?? 0 ?>,
                        <?= $totals['overtime'] ?? 0 ?>,
                        <?= ($totals['bpjs'] ?? 0) + ($totals['pph21'] ?? 0) + ($totals['other_deductions'] ?? 0) ?>
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#dc3545']
                }]
            }
        });
        
        // Department Chart
        new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($departmentTotals ?? [], 'name')) ?>,
                datasets: [{
                    label: 'Total Gaji',
                    data: <?= json_encode(array_column($departmentTotals ?? [], 'total')) ?>,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>
