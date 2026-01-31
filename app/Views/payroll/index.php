<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Penggajian</h1>
    <a href="<?= base_url('payroll/process') ?>" class="btn btn-primary">
        <i class="fas fa-calculator me-2"></i> Proses Penggajian
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="paid" <?= $status == 'paid' ? 'selected' : '' ?>>Dibayar</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="<?= base_url('payroll/export') ?>?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-success">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<?php if (!empty($payrolls)): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= count($payrolls) ?></h3>
                <small>Total Pegawai</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>Rp <?= number_format(array_sum(array_column($payrolls, 'gross_salary')), 0, ',', '.') ?></h3>
                <small>Total Gaji Kotor</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3>Rp <?= number_format(array_sum(array_column($payrolls, 'total_deductions')), 0, ',', '.') ?></h3>
                <small>Total Potongan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>Rp <?= number_format(array_sum(array_column($payrolls, 'net_salary')), 0, ',', '.') ?></h3>
                <small>Total Gaji Bersih</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="payrollTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pegawai</th>
                        <th>Departemen</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Potongan</th>
                        <th>Gaji Bersih</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payrolls)): ?>
                    <?php foreach ($payrolls as $i => $payroll): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= esc($payroll['full_name']) ?></strong>
                            <br><small class="text-muted"><?= esc($payroll['employee_code']) ?></small>
                        </td>
                        <td><?= esc($payroll['department_name'] ?? '-') ?></td>
                        <td>Rp <?= number_format($payroll['base_salary'], 0, ',', '.') ?></td>
                        <td class="text-success">+Rp <?= number_format($payroll['total_earnings'], 0, ',', '.') ?></td>
                        <td class="text-danger">-Rp <?= number_format($payroll['total_deductions'], 0, ',', '.') ?></td>
                        <td><strong>Rp <?= number_format($payroll['net_salary'], 0, ',', '.') ?></strong></td>
                        <td>
                            <?php
                            $statusClass = ['draft' => 'secondary', 'calculated' => 'primary', 'approved' => 'info', 'paid' => 'success'];
                            $statusText = ['draft' => 'Draft', 'calculated' => 'Terhitung', 'approved' => 'Disetujui', 'paid' => 'Dibayar'];
                            ?>
                            <span class="badge bg-<?= $statusClass[$payroll['status']] ?>">
                                <?= $statusText[$payroll['status']] ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('payroll/' . $payroll['id']) ?>" class="btn btn-sm btn-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($payroll['status'] === 'draft'): ?>
                            <button class="btn btn-sm btn-success" onclick="approvePayroll(<?= $payroll['id'] ?>)" title="Setujui">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php elseif ($payroll['status'] === 'approved'): ?>
                            <button class="btn btn-sm btn-primary" onclick="payPayroll(<?= $payroll['id'] ?>)" title="Bayar">
                                <i class="fas fa-money-bill"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            Belum ada data penggajian untuk periode ini.
                            <br><a href="<?= base_url('payroll/process') ?>" class="btn btn-primary btn-sm mt-2">Proses Penggajian</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#payrollTable').DataTable({
            order: [[1, 'asc']]
        });
    });
    
    function approvePayroll(id) {
        Swal.fire({
            title: 'Setujui Gaji?',
            text: 'Gaji yang disetujui tidak dapat diubah lagi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('payroll/approve') ?>/' + id,
                    method: 'POST',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            location.reload();
                        } else {
                            showToast('error', response.message);
                        }
                    }
                });
            }
        });
    }
    
    function payPayroll(id) {
        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: 'Tandai gaji ini sebagai sudah dibayar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sudah Dibayar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('payroll/pay') ?>/' + id,
                    method: 'POST',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            location.reload();
                        } else {
                            showToast('error', response.message);
                        }
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
