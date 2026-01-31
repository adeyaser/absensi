<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Laporan Karyawan</h1>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
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
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($status ?? '') == 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($status ?? '') == 'inactive' ? 'selected' : '' ?>>Non-Aktif</option>
                    <option value="resigned" <?= ($status ?? '') == 'resigned' ? 'selected' : '' ?>>Resign</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
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
                <h4><?= $summary['total'] ?? 0 ?></h4>
                <small>Total Karyawan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?= $summary['active'] ?? 0 ?></h4>
                <small>Karyawan Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4><?= $summary['inactive'] ?? 0 ?></h4>
                <small>Karyawan Non-Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?= $summary['by_gender']['male'] ?? 0 ?> / <?= $summary['by_gender']['female'] ?? 0 ?></h4>
                <small>Laki-laki / Perempuan</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Department Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i> Karyawan per Departemen
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Contract Type Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Karyawan per Tipe Kontrak
            </div>
            <div class="card-body">
                <canvas id="contractChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-users me-2"></i> Daftar Karyawan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="employeesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Gender</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Tanggal Bergabung</th>
                        <th>Kontrak</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $i => $emp): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($emp['employee_id']) ?></strong></td>
                        <td><?= esc($emp['full_name']) ?></td>
                        <td><?= esc($emp['nik'] ?? '-') ?></td>
                        <td>
                            <i class="fas fa-<?= $emp['gender'] === 'male' ? 'mars text-primary' : 'venus text-danger' ?>"></i>
                            <?= $emp['gender'] === 'male' ? 'L' : 'P' ?>
                        </td>
                        <td><?= esc($emp['department_name'] ?? '-') ?></td>
                        <td><?= esc($emp['position_name'] ?? '-') ?></td>
                        <td><?= $emp['join_date'] ? date('d M Y', strtotime($emp['join_date'])) : '-' ?></td>
                        <td>
                            <?php
                            $contractBadge = [
                                'permanent' => 'success',
                                'contract' => 'warning',
                                'probation' => 'info',
                                'internship' => 'secondary',
                            ];
                            $contractText = [
                                'permanent' => 'Tetap',
                                'contract' => 'Kontrak',
                                'probation' => 'Probation',
                                'internship' => 'Magang',
                            ];
                            ?>
                            <span class="badge bg-<?= $contractBadge[$emp['contract_type']] ?? 'secondary' ?>">
                                <?= $contractText[$emp['contract_type']] ?? $emp['contract_type'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($emp['employment_status'] === 'active'): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php elseif ($emp['employment_status'] === 'inactive'): ?>
                            <span class="badge bg-warning">Non-Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger"><?= ucfirst($emp['employment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">Tidak ada data karyawan</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function() {
        $('#employeesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });
        
        // Department Chart
        new Chart(document.getElementById('departmentChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($summary['by_department'] ?? [])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($summary['by_department'] ?? [])) ?>,
                    backgroundColor: [
                        '#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', 
                        '#6f42c1', '#fd7e14', '#20c997', '#d63384', '#6c757d'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Contract Chart
        new Chart(document.getElementById('contractChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($k) {
                    $labels = ['permanent' => 'Tetap', 'contract' => 'Kontrak', 'probation' => 'Probation', 'internship' => 'Magang'];
                    return $labels[$k] ?? ucfirst($k);
                }, array_keys($summary['by_contract'] ?? []))) ?>,
                datasets: [{
                    label: 'Jumlah Karyawan',
                    data: <?= json_encode(array_values($summary['by_contract'] ?? [])) ?>,
                    backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#6c757d']
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>
