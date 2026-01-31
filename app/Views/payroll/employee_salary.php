<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Gaji Pegawai</h1>
    <div class="btn-group">
        <a href="<?= base_url('payroll/export') ?>" class="btn btn-outline-primary">
            <i class="fas fa-download me-2"></i> Export CSV
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="employeeSalaryTable">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Pegawai</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th class="text-end">Gaji Pokok</th>
                        <th>Informasi Bank</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $i => $emp): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= esc($emp['full_name']) ?></strong>
                            <br><small class="text-muted"><?= esc($emp['employee_code']) ?></small>
                        </td>
                        <td><?= esc($emp['department_name'] ?? '-') ?></td>
                        <td><?= esc($emp['position_name'] ?? '-') ?></td>
                        <td class="text-end">
                            <?php if ($emp['base_salary']): ?>
                            <strong>Rp <?= number_format($emp['base_salary'], 0, ',', '.') ?></strong>
                            <?php else: ?>
                            <span class="text-muted">Belum diatur</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['bank_name']): ?>
                            <?= esc($emp['bank_name']) ?>
                            <br><small class="text-muted"><?= esc($emp['bank_account']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['employment_status'] === 'active'): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($emp['employment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('payroll/employee-salary/edit/' . $emp['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i> Atur Gaji
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data karyawan</td>
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
        $('#employeeSalaryTable').DataTable();
    });

    // Handle auto-redirect if employee_id is in URL
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const employeeId = urlParams.get('employee_id');
        if (employeeId) {
            window.location.href = '<?= base_url('payroll/employee-salary/edit') ?>/' + employeeId;
        }
    }
</script>
<?= $this->endSection() ?>
