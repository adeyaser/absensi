<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><?= $employee ? 'Edit' : 'Tambah' ?> Pegawai</h1>
    <a href="<?= base_url('employees') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form action="<?= $employee ? base_url('employees/update/' . $employee['id']) : base_url('employees/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Data Pribadi -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> Data Pribadi
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= old('full_name', $employee['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK (KTP)</label>
                            <input type="text" name="nik" class="form-control" value="<?= old('nik', $employee['nik'] ?? '') ?>" maxlength="16">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L" <?= old('gender', $employee['gender'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= old('gender', $employee['gender'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="birth_place" class="form-control" value="<?= old('birth_place', $employee['birth_place'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-control" value="<?= old('birth_date', $employee['birth_date'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Agama</label>
                            <select name="religion" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Islam" <?= old('religion', $employee['religion'] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                                <option value="Kristen" <?= old('religion', $employee['religion'] ?? '') == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                                <option value="Katolik" <?= old('religion', $employee['religion'] ?? '') == 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                                <option value="Hindu" <?= old('religion', $employee['religion'] ?? '') == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                                <option value="Buddha" <?= old('religion', $employee['religion'] ?? '') == 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                                <option value="Konghucu" <?= old('religion', $employee['religion'] ?? '') == 'Konghucu' ? 'selected' : '' ?>>Konghucu</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status Pernikahan</label>
                            <select name="marital_status" class="form-select">
                                <option value="single" <?= old('marital_status', $employee['marital_status'] ?? '') == 'single' ? 'selected' : '' ?>>Belum Menikah</option>
                                <option value="married" <?= old('marital_status', $employee['marital_status'] ?? '') == 'married' ? 'selected' : '' ?>>Menikah</option>
                                <option value="divorced" <?= old('marital_status', $employee['marital_status'] ?? '') == 'divorced' ? 'selected' : '' ?>>Cerai</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?= old('phone', $employee['phone'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email', $employee['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"><?= old('address', $employee['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Data Kepegawaian -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-briefcase me-2"></i> Data Kepegawaian
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departemen <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select select2" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= old('department_id', $employee['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= esc($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="position_id" id="position_id" class="form-select select2" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>" data-department="<?= $pos['department_id'] ?>" <?= old('position_id', $employee['position_id'] ?? '') == $pos['id'] ? 'selected' : '' ?>>
                                    <?= esc($pos['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Atasan Langsung</label>
                            <select name="supervisor_id" id="supervisor_id" class="form-select select2">
                                <option value="">-- Pilih Atasan --</option>
                                <?php foreach ($employees ?? [] as $emp): ?>
                                    <?php if (!$employee || $emp['id'] != $employee['id']): ?>
                                    <option value="<?= $emp['id'] ?>" <?= old('supervisor_id', $employee['supervisor_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                                        <?= esc($emp['full_name']) ?> (<?= esc($emp['position_name'] ?? 'N/A') ?>)
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pilih atasan langsung untuk karyawan ini</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status Kepegawaian</label>
                            <select name="employment_status" class="form-select">
                                <option value="permanent" <?= old('employment_status', $employee['employment_status'] ?? '') == 'permanent' ? 'selected' : '' ?>>Tetap</option>
                                <option value="contract" <?= old('employment_status', $employee['employment_status'] ?? '') == 'contract' ? 'selected' : '' ?>>Kontrak</option>
                                <option value="internship" <?= old('employment_status', $employee['employment_status'] ?? '') == 'internship' ? 'selected' : '' ?>>Magang</option>
                                <option value="probation" <?= old('employment_status', $employee['employment_status'] ?? '') == 'probation' ? 'selected' : '' ?>>Percobaan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" class="form-control" value="<?= old('join_date', $employee['join_date'] ?? '') ?>" required>
                        </div>
                        <?php if ($employee): ?>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Resign</label>
                            <input type="date" name="resign_date" class="form-control" value="<?= old('resign_date', $employee['resign_date'] ?? '') ?>">
                        </div>
                        <?php else: ?>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gaji Pokok</label>
                            <input type="number" name="base_salary" class="form-control" value="<?= old('base_salary', '') ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Data Bank & BPJS -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-university me-2"></i> Data Bank & BPJS
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Bank</label>
                            <select name="bank_name" class="form-select">
                                <option value="">-- Pilih Bank --</option>
                                <option value="BCA" <?= old('bank_name', $employee['bank_name'] ?? '') == 'BCA' ? 'selected' : '' ?>>BCA</option>
                                <option value="BNI" <?= old('bank_name', $employee['bank_name'] ?? '') == 'BNI' ? 'selected' : '' ?>>BNI</option>
                                <option value="BRI" <?= old('bank_name', $employee['bank_name'] ?? '') == 'BRI' ? 'selected' : '' ?>>BRI</option>
                                <option value="Mandiri" <?= old('bank_name', $employee['bank_name'] ?? '') == 'Mandiri' ? 'selected' : '' ?>>Mandiri</option>
                                <option value="CIMB Niaga" <?= old('bank_name', $employee['bank_name'] ?? '') == 'CIMB Niaga' ? 'selected' : '' ?>>CIMB Niaga</option>
                                <option value="Danamon" <?= old('bank_name', $employee['bank_name'] ?? '') == 'Danamon' ? 'selected' : '' ?>>Danamon</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. Rekening</label>
                            <input type="text" name="bank_account" class="form-control" value="<?= old('bank_account', $employee['bank_account'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Atas Nama</label>
                            <input type="text" name="bank_holder" class="form-control" value="<?= old('bank_holder', $employee['bank_holder'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NPWP</label>
                            <input type="text" name="npwp" class="form-control" value="<?= old('npwp', $employee['npwp'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">BPJS Kesehatan</label>
                            <input type="text" name="bpjs_kesehatan" class="form-control" value="<?= old('bpjs_kesehatan', $employee['bpjs_kesehatan'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">BPJS Ketenagakerjaan</label>
                            <input type="text" name="bpjs_ketenagakerjaan" class="form-control" value="<?= old('bpjs_ketenagakerjaan', $employee['bpjs_ketenagakerjaan'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Foto -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-camera me-2"></i> Foto
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($employee && $employee['photo']): ?>
                        <img src="<?= base_url('writable/uploads/' . $employee['photo']) ?>" alt="Photo" class="img-fluid rounded" style="max-height: 200px;" id="photoPreview">
                        <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;" id="photoPreviewWrapper">
                            <i class="fas fa-user fa-5x text-muted" id="photoIcon"></i>
                            <img src="" alt="" class="img-fluid rounded d-none" style="max-height: 200px;" id="photoPreview">
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="photo" id="photoInput" class="form-control" accept="image/*">
                    <small class="text-muted">Max 2MB, format: JPG, PNG</small>
                </div>
            </div>
            
            <?php if (!$employee): ?>
            <!-- Create User Account -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-shield me-2"></i> Akun User
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="create_user" id="createUser" value="1">
                        <label class="form-check-label" for="createUser">Buat akun user</label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Username akan dibuat dari nama pegawai, password default: <code>password123</code>
                    </small>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($employee): ?>
            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> Status
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= old('is_active', $employee['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Pegawai Aktif</label>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Simpan
                        </button>
                        <a href="<?= base_url('employees') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Photo preview
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                const icon = document.getElementById('photoIcon');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (icon) icon.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Filter positions by department
    document.getElementById('department_id').addEventListener('change', function() {
        const departmentId = this.value;
        const positionSelect = document.getElementById('position_id');
        const options = positionSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '' || option.dataset.department == departmentId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        positionSelect.value = '';
    });
</script>
<?= $this->endSection() ?>
