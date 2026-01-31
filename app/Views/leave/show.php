<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Detail Pengajuan Cuti</h1>
    <a href="<?= base_url('leave') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i> Informasi Pengajuan</span>
                <?php
                $statusClass = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'secondary'
                ];
                $statusText = [
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled' => 'Dibatalkan'
                ];
                ?>
                <span class="badge bg-<?= $statusClass[$request['status']] ?>">
                    <?= $statusText[$request['status']] ?>
                </span>
            </div>
            <div class="card-body">
                <table class="table mb-0">
                    <tr>
                        <th width="200">Karyawan</th>
                        <td>: <?= esc($request['employee_name']) ?> (<?= esc($request['employee_code']) ?>)</td>
                    </tr>
                    <tr>
                        <th>Jenis Cuti</th>
                        <td>: <?= esc($request['leave_type_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Cuti</th>
                        <td>: <?= date('d/m/Y', strtotime($request['start_date'])) ?> 
                            <?php if ($request['start_date'] !== $request['end_date']): ?>
                            sampai <?= date('d/m/Y', strtotime($request['end_date'])) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Hari</th>
                        <td>: <span class="badge bg-primary"><?= $request['total_days'] ?> hari</span></td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>: <?= nl2br(esc($request['reason'])) ?></td>
                    </tr>
                    <tr>
                        <th>Tgl. Pengajuan</th>
                        <td>: <?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                    </tr>
                    <?php if ($request['attachment']): ?>
                    <tr>
                        <th>Lampiran</th>
                        <td>: 
                            <a href="<?= base_url('writable/uploads/' . $request['attachment']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-download me-1"></i> Lihat Lampiran
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <?php if ($request['status'] === 'rejected' && $request['rejection_reason']): ?>
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between">
                <span><i class="fas fa-times-circle me-2"></i> Alasan Penolakan</span>
                <small>Ditolak oleh: <?= esc($request['approved_by_name'] ?? 'Admin') ?></small>
            </div>
            <div class="card-body">
                <?= nl2br(esc($request['rejection_reason'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($request['status'] === 'approved'): ?>
        <div class="card mt-4 border-success">
            <div class="card-body py-2">
                <i class="fas fa-check-circle text-success me-2"></i>
                Disetujui oleh <strong><?= esc($request['approved_by_name'] ?? 'Admin') ?></strong> pada <?= date('d/m/Y H:i', strtotime($request['approved_at'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog me-2"></i> Tindakan
            </div>
            <div class="card-body">
                <?php if ($request['status'] === 'pending' && $request['employee_id'] == $currentUser['employee_id']): ?>
                <button class="btn btn-danger w-100 mb-3" onclick="cancelLeave(<?= $request['id'] ?>)">
                    <i class="fas fa-times me-2"></i> Batalkan Pengajuan
                </button>
                <?php endif; ?>
                
                <p class="text-muted small mb-0">
                    Jika ada kesalahan data atau ingin mengubah jadwal, silakan hubungi bagian HRD atau atasan langsung.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function cancelLeave(id) {
        Swal.fire({
            title: 'Batalkan Pengajuan?',
            text: 'Pengajuan cuti ini akan dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('leave/cancel') ?>/' + id,
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(() => location.reload(), 1000);
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
