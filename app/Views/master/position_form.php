<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('master/positions') ?>">Master Jabatan</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $position ? 'Edit Jabatan' : 'Tambah Jabatan' ?></li>
                    </ol>
                </nav>
                <h1><?= $position ? 'Edit Jabatan' : 'Tambah Jabatan' ?></h1>
            </div>
            <div>
                <a href="<?= base_url('master/positions') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <form action="<?= base_url('master/positions/store') ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($position): ?>
                <input type="hidden" name="id" value="<?= $position['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3 border-bottom pb-2">Informasi Jabatan</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="<?= esc($position['code'] ?? '') ?>" required maxlength="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Level <span class="text-danger">*</span></label>
                                    <input type="number" name="level" class="form-control" value="<?= $position['level'] ?? 1 ?>" min="1" max="10" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= esc($position['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Departemen <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        <option value="">-- Pilih Departemen --</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>" <?= ($position['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>><?= esc($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="base_salary" class="form-control" value="<?= $position['base_salary'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="description" class="form-control" rows="3"><?= esc($position['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= ($position['is_active'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">Jabatan Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3 border-bottom pb-2">Komponen Pendapatan & Potongan</h6>
                            
                            <!-- Tabs -->
                            <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link py-1 active" data-bs-toggle="pill" href="#earning-pane">Pendapatan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-1" data-bs-toggle="pill" href="#deduction-pane">Potongan</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Earnings -->
                                <div class="tab-pane fade show active" id="earning-pane">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th width="40">On</th>
                                                <th>Nama Komponen</th>
                                                <th width="200">Jumlah / Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($earningComponents ?? [] as $comp): 
                                                $assigned = null;
                                                if ($position && !empty($position['salary_components'])) {
                                                    foreach ($position['salary_components'] as $sc) {
                                                        if ($sc['component_id'] == $comp['id']) {
                                                            $assigned = $sc;
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input comp-checkbox" type="checkbox" 
                                                               name="components[<?= $comp['id'] ?>][enabled]" 
                                                               value="1" data-comp-id="<?= $comp['id'] ?>"
                                                               <?= $assigned ? 'checked' : '' ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold small"><?= esc($comp['name']) ?></div>
                                                    <small class="text-muted"><?= $comp['calculation_type'] ?></small>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><?= $comp['calculation_type'] === 'percentage' ? '%' : 'Rp' ?></span>
                                                        <input type="number" name="components[<?= $comp['id'] ?>][amount]" 
                                                               class="form-control comp-amount" data-comp-id="<?= $comp['id'] ?>"
                                                               value="<?= $assigned['amount'] ?? $comp['default_value'] ?>" 
                                                               <?= $assigned ? '' : 'disabled' ?>>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Deductions -->
                                <div class="tab-pane fade" id="deduction-pane">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th width="40">On</th>
                                                <th>Nama Komponen</th>
                                                <th width="200">Jumlah / Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($deductionComponents ?? [] as $comp): 
                                                $assigned = null;
                                                if ($position && !empty($position['salary_components'])) {
                                                    foreach ($position['salary_components'] as $sc) {
                                                        if ($sc['component_id'] == $comp['id']) {
                                                            $assigned = $sc;
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input comp-checkbox" type="checkbox" 
                                                               name="components[<?= $comp['id'] ?>][enabled]" 
                                                               value="1" data-comp-id="<?= $comp['id'] ?>"
                                                               <?= $assigned ? 'checked' : '' ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold small"><?= esc($comp['name']) ?></div>
                                                    <small class="text-muted"><?= $comp['calculation_type'] ?></small>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><?= $comp['calculation_type'] === 'percentage' ? '%' : 'Rp' ?></span>
                                                        <input type="number" name="components[<?= $comp['id'] ?>][amount]" 
                                                               class="form-control comp-amount" data-comp-id="<?= $comp['id'] ?>"
                                                               value="<?= $assigned['amount'] ?? $comp['default_value'] ?>" 
                                                               <?= $assigned ? '' : 'disabled' ?>>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('master/positions') ?>" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Simpan Jabatan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('.comp-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const compId = this.dataset.compId;
            const amountInput = document.querySelector(`.comp-amount[data-comp-id="${compId}"]`);
            amountInput.disabled = !this.checked;
        });
    });
</script>
<?= $this->endSection() ?>
