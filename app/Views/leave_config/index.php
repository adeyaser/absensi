<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Konfigurasi Cuti Karyawan</h1>
    <div>
        <form action="" method="get" class="d-flex gap-2">
            <select name="year" class="form-select" onchange="this.form.submit()">
                <?php 
                $currentYear = date('Y');
                for($y = $currentYear - 1; $y <= $currentYear + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="department_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Departemen</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>>
                        <?= $dept['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Halaman ini berfungsi untuk <strong>memberikan atau mengatur jumlah jatah cuti (kuota)</strong> khusus untuk setiap karyawan. <br>
    Nilai yang Anda masukkan di sini akan <strong>menimpa (override)</strong> kuota default dari jenis cuti tersebut. 
    Jika tidak diatur, karyawan akan mendapatkan kuota standar sesuai ketentuan master data jenis cuti.
    <div class="mt-2">
        <a href="<?= base_url('master/leave-types') ?>" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-cog me-1"></i> Atur Default Jenis Cuti
        </a>
        <a href="<?= base_url('employees') ?>" class="btn btn-sm btn-outline-success ms-2">
            <i class="fas fa-user-plus me-1"></i> Tambah Karyawan Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle">Karyawan</th>
                        <th rowspan="2" class="align-middle">Departemen</th>
                        <th colspan="<?= count($leaveTypes) ?>" class="text-center">Kuota Tahun <?= $year ?></th>
                        <th rowspan="2" class="align-middle text-center">Aksi</th>
                    </tr>
                    <tr>
                        <?php foreach($leaveTypes as $type): ?>
                            <th class="text-center"><?= $type['name'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($employees)): ?>
                        <tr><td colspan="<?= 3 + count($leaveTypes) ?>" class="text-center">Tidak ada data karyawan</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <strong><?= esc($emp['full_name']) ?></strong><br>
                                <small class="text-muted"><?= esc($emp['employee_code']) ?></small>
                            </td>
                            <td><?= esc($emp['department_name']) ?></td>
                            <?php foreach($leaveTypes as $type): ?>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">
                                        <?= $emp['quotas'][$type['id']] ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <a href="<?= base_url('leave-config/edit/' . $emp['id'] . '?year=' . $year) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
