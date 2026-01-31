<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Input Absensi Manual</h1>
    <a href="<?= base_url('attendance/recap') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i> Form Input Manual
            </div>
            <div class="card-body">
                <form action="<?= base_url('attendance/manual') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select select2" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php foreach ($employees ?? [] as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= esc($emp['employee_id']) ?> - <?= esc($emp['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" name="clock_in" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Pulang</label>
                            <input type="time" name="clock_out" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="leave">Cuti</option>
                            <option value="sick">Sakit</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Panduan
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i> Kapan menggunakan input manual?</h6>
                    <ul class="mb-0">
                        <li>Karyawan lupa absen</li>
                        <li>Masalah teknis pada mesin absensi</li>
                        <li>Karyawan bekerja di luar kantor</li>
                        <li>Koreksi data absensi</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i> Perhatian</h6>
                    <ul class="mb-0">
                        <li>Pastikan data yang diinput sudah benar</li>
                        <li>Jika data sudah ada, akan ditimpa dengan data baru</li>
                        <li>Semua perubahan akan tercatat di log sistem</li>
                    </ul>
                </div>
                
                <h6 class="mt-4">Status Absensi:</h6>
                <table class="table table-sm">
                    <tr>
                        <td><span class="badge bg-success">Hadir</span></td>
                        <td>Karyawan hadir tepat waktu</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-warning">Terlambat</span></td>
                        <td>Karyawan hadir tapi terlambat</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-danger">Tidak Hadir</span></td>
                        <td>Karyawan tidak hadir tanpa keterangan</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-info">Cuti</span></td>
                        <td>Karyawan mengambil cuti</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-secondary">Sakit</span></td>
                        <td>Karyawan tidak hadir karena sakit</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih karyawan...',
            allowClear: true
        });
    });
</script>
<?= $this->endSection() ?>
