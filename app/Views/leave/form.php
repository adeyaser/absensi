<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Ajukan Cuti</h1>
    <a href="<?= base_url('leave') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="<?= base_url('leave/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
                        <select name="leave_type_id" id="leaveTypeSelect" class="form-select" required>
                            <option value="">-- Pilih Jenis Cuti --</option>
                            <?php foreach ($leaveTypes as $type): ?>
                            <?php $maxDays = $type['max_days'] ?? ($type['quota_per_year'] ?? ($type['quota'] ?? 1)); ?>
                            <option value="<?= $type['id'] ?>" data-max="<?= $maxDays ?>" <?= old('leave_type_id') == $type['id'] ? 'selected' : '' ?>>
                                <?= esc($type['name']) ?> (Max: <?= $maxDays ?> hari)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="<?= old('start_date') ?>" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="<?= old('end_date') ?>" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Hari</label>
                        <input type="text" id="totalDays" class="form-control" value="0 hari" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Jelaskan alasan pengajuan cuti..."><?= old('reason') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Lampiran</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Opsional. Format: PDF, JPG, PNG. Max 2MB.</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i> Ajukan Cuti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Leave Balance -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar me-2"></i> Sisa Cuti Anda
            </div>
            <div class="card-body">
                <?php if (!empty($leaveBalance)): ?>
                <ul class="list-group list-group-flush" id="balanceList">
                    <li class="list-group-item text-center text-muted" id="balancePlaceholder">
                        Pilih jenis cuti untuk melihat sisa jatah
                    </li>
                    <?php foreach ($leaveBalance as $balance): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center balance-item" data-id="<?= $balance['leave_type_id'] ?>" style="display: none !important;">
                        <?= esc($balance['leave_type_name']) ?>
                        <span class="badge bg-primary rounded-pill"><?= $balance['remaining'] ?? $balance['quota'] ?> hari</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">Tidak ada data sisa jatah cuti</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Info -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Informasi
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Pengajuan cuti harus diajukan minimal H-3 hari kerja.</li>
                    <li class="mb-2">Cuti akan diproses oleh atasan langsung.</li>
                    <li class="mb-2">Lampiran wajib untuk cuti sakit (surat dokter).</li>
                    <li>Status pengajuan dapat dilihat di halaman riwayat cuti.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const totalDaysInput = document.getElementById('totalDays');
    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const balancePlaceholder = document.getElementById('balancePlaceholder');
    const balanceItems = document.querySelectorAll('.balance-item');
    
    function calculateDays() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (startDate && endDate && endDate >= startDate) {
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalDaysInput.value = diffDays + ' hari';
        } else {
            totalDaysInput.value = '0 hari';
        }
    }

    function updateBalanceDisplay() {
        const selectedId = leaveTypeSelect.value;
        
        if (!selectedId) {
            if (balancePlaceholder) balancePlaceholder.style.display = 'block';
            balanceItems.forEach(item => item.setAttribute('style', 'display: none !important'));
        } else {
            if (balancePlaceholder) balancePlaceholder.style.display = 'none';
            balanceItems.forEach(item => {
                if (item.getAttribute('data-id') == selectedId) {
                    item.setAttribute('style', 'display: flex !important');
                } else {
                    item.setAttribute('style', 'display: none !important');
                }
            });
        }
    }
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        calculateDays();
    });
    
    leaveTypeSelect.addEventListener('change', updateBalanceDisplay);
    
    // Run on initial load
    updateBalanceDisplay();

    endDateInput.addEventListener('change', calculateDays);
</script>
<?= $this->endSection() ?>
