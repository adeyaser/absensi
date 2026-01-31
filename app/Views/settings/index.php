<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Pengaturan Umum</h1>
</div>

<form action="<?= base_url('settings/update') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Company Info -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-building me-2"></i> Informasi Perusahaan
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="company_name" class="form-control" value="<?= esc($settings['company_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="company_email" class="form-control" value="<?= esc($settings['company_email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="company_phone" class="form-control" value="<?= esc($settings['company_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" name="company_city" class="form-control" value="<?= esc($settings['company_city'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="company_address" class="form-control" rows="2"><?= esc($settings['company_address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Pengaturan Absensi
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Masuk Default</label>
                            <input type="time" name="work_start_time" class="form-control" value="<?= $settings['work_start_time'] ?? '08:00' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Pulang Default</label>
                            <input type="time" name="work_end_time" class="form-control" value="<?= $settings['work_end_time'] ?? '17:00' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Toleransi Terlambat (menit)</label>
                            <input type="number" name="late_tolerance" class="form-control" value="<?= $settings['late_tolerance'] ?? '15' ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Radius Lokasi (meter)</label>
                            <input type="number" name="attendance_radius" class="form-control" value="<?= $settings['attendance_radius'] ?? '100' ?>">
                            <small class="text-muted">Jarak maksimum dari lokasi kantor untuk absensi</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="attendance_require_photo" value="1" <?= ($settings['attendance_require_photo'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label">Wajib foto saat absensi</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="attendance_require_location" value="1" <?= ($settings['attendance_require_location'] ?? '1') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label">Wajib validasi lokasi</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payroll Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-money-bill me-2"></i> Pengaturan Penggajian
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Gajian</label>
                            <input type="number" name="payroll_date" class="form-control" min="1" max="31" value="<?= $settings['payroll_date'] ?? '25' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari Kerja/Bulan</label>
                            <input type="number" name="work_days_per_month" class="form-control" value="<?= $settings['work_days_per_month'] ?? '22' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Kerja/Hari</label>
                            <input type="number" name="work_hours_per_day" class="form-control" value="<?= $settings['work_hours_per_day'] ?? '8' ?>">
                        </div>
                    </div>
                    
                    <hr>
                    <h6>BPJS Kesehatan</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Persentase Perusahaan (%)</label>
                            <input type="number" step="0.01" name="bpjs_kesehatan_company" class="form-control" value="<?= $settings['bpjs_kesehatan_company'] ?? '4' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Persentase Karyawan (%)</label>
                            <input type="number" step="0.01" name="bpjs_kesehatan_employee" class="form-control" value="<?= $settings['bpjs_kesehatan_employee'] ?? '1' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batas Gaji Max</label>
                            <input type="number" name="bpjs_kesehatan_max_salary" class="form-control" value="<?= $settings['bpjs_kesehatan_max_salary'] ?? '12000000' ?>">
                        </div>
                    </div>
                    
                    <hr>
                    <h6>BPJS Ketenagakerjaan</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">JKK (%)</label>
                            <input type="number" step="0.01" name="bpjs_jkk" class="form-control" value="<?= $settings['bpjs_jkk'] ?? '0.24' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">JKM (%)</label>
                            <input type="number" step="0.01" name="bpjs_jkm" class="form-control" value="<?= $settings['bpjs_jkm'] ?? '0.3' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">JHT Perusahaan (%)</label>
                            <input type="number" step="0.01" name="bpjs_jht_company" class="form-control" value="<?= $settings['bpjs_jht_company'] ?? '3.7' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">JHT Karyawan (%)</label>
                            <input type="number" step="0.01" name="bpjs_jht_employee" class="form-control" value="<?= $settings['bpjs_jht_employee'] ?? '2' ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">JP Perusahaan (%)</label>
                            <input type="number" step="0.01" name="bpjs_jp_company" class="form-control" value="<?= $settings['bpjs_jp_company'] ?? '2' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">JP Karyawan (%)</label>
                            <input type="number" step="0.01" name="bpjs_jp_employee" class="form-control" value="<?= $settings['bpjs_jp_employee'] ?? '1' ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Logo -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-image me-2"></i> Logo Perusahaan
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($settings['company_logo'])): ?>
                    <img src="<?= base_url('writable/uploads/' . $settings['company_logo']) ?>" alt="Logo" class="img-fluid mb-3" style="max-height: 150px;">
                    <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                        <i class="fas fa-image fa-4x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="company_logo" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG. Max 1MB.</small>
                </div>
            </div>
            
            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
