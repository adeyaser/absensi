<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1>Edit Kuota Cuti</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <strong><?= esc($employee['full_name']) ?></strong> (<?= esc($employee['employee_code']) ?>)
                <span class="float-end badge bg-primary"><?= $year ?></span>
            </div>
            <div class="card-body">
                <form action="<?= base_url('leave-config/update/' . $employee['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="year" value="<?= $year ?>">
                    
                    <div class="alert alert-info py-2">
                        <small>Masukkan jumlah total kuota cuti untuk tahun ini. Angka ini akan menggantikan (override) kuota standar untuk karyawan ini.</small>
                    </div>
                    
                    <?php foreach ($leaveTypes as $type): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= esc($type['name']) ?></label>
                        <input type="number" name="quotas[<?= $type['id'] ?>]" 
                               class="form-control" 
                               value="<?= $quotas[$type['id']] ?>" required min="0">
                        <div class="form-text">Default: <?= $type['quota'] ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="<?= base_url('leave-config?year=' . $year) ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
