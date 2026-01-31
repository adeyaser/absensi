<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Proses Penggajian</h1>
    <a href="<?= base_url('payroll') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-calculator me-2"></i> Parameter Penggajian
    </div>
    <div class="card-body">
        <form action="<?= base_url('payroll/calculate') ?>" method="post" id="processForm">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Periode Bulan <span class="text-danger">*</span></label>
                    <select name="month" class="form-select" required>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= date('n') == $i ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tahun <span class="text-danger">*</span></label>
                    <select name="year" class="form-select" required>
                        <?php for ($i = date('Y'); $i >= date('Y') - 2; $i--): ?>
                        <option value="<?= $i ?>" <?= date('Y') == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select">
                        <option value="">Semua Departemen</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= esc($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pegawai</label>
                    <select name="employee_ids[]" class="form-select select2" multiple>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= esc($emp['employee_code'] . ' - ' . $emp['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Kosongkan untuk memproses semua pegawai</small>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Sistem akan menghitung gaji berdasarkan:
                <ul class="mb-0 mt-2">
                    <li>Gaji pokok dan komponen gaji yang ditetapkan untuk masing-masing pegawai</li>
                    <li>Data kehadiran (hadir, terlambat, tidak hadir)</li>
                    <li>Data cuti dan sakit</li>
                    <li>Data lembur (jika ada)</li>
                    <li>Potongan BPJS dan PPh21</li>
                </ul>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-calculator me-2"></i> Hitung Gaji
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Recent Payroll -->
<?php if (!empty($recentPayrolls)): ?>
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-history me-2"></i> Penggajian Terakhir
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Jumlah Pegawai</th>
                        <th>Total Gaji</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPayrolls as $payroll): ?>
                    <tr>
                        <td><?= date('F Y', mktime(0, 0, 0, $payroll['period_month'], 1, $payroll['period_year'])) ?></td>
                        <td><?= $payroll['employee_count'] ?> pegawai</td>
                        <td>Rp <?= number_format($payroll['total_salary'], 0, ',', '.') ?></td>
                        <td>
                            <span class="badge bg-<?= $payroll['status'] === 'paid' ? 'success' : ($payroll['status'] === 'approved' ? 'info' : 'secondary') ?>">
                                <?= ucfirst($payroll['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('payroll') ?>?month=<?= $payroll['period_month'] ?>&year=<?= $payroll['period_year'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih pegawai (opsional)'
        });
    });
    
    document.getElementById('processForm').addEventListener('submit', function() {
        showLoading();
    });
</script>
<?= $this->endSection() ?>
