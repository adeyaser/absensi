<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Pengajuan Cuti</h1>
    <a href="<?= base_url('leave/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Ajukan Cuti
    </a>
</div>



<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i> Riwayat Pengajuan Cuti
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="leaveTable">
                <thead>
                    <tr>
                        <th>Tgl. Pengajuan</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Cuti</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leaves)): ?>
                    <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($leave['created_at'])) ?></td>
                        <td><?= esc($leave['leave_type_name']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($leave['start_date'])) ?>
                            <?php if ($leave['start_date'] !== $leave['end_date']): ?>
                            - <?= date('d/m/Y', strtotime($leave['end_date'])) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $leave['total_days'] ?> hari</td>
                        <td><?= esc(substr($leave['reason'], 0, 50)) ?><?= strlen($leave['reason']) > 50 ? '...' : '' ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'cancelled' => 'secondary'
                            ];
                            $statusText = [
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusClass[$leave['status']] ?>">
                                <?= $statusText[$leave['status']] ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('leave/' . $leave['id']) ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($leave['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-danger" onclick="cancelLeave(<?= $leave['id'] ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (empty($leaves)): ?>
            <div class="py-3 text-center text-muted">Belum ada pengajuan cuti</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Debug: check header vs each row column counts and highlight mismatches
        try {
            const headerCount = $('#leaveTable thead tr').first().children('th').length;
            console.log('leaveTable header columns:', headerCount);
            $('#leaveTable tbody tr').each(function(idx) {
                const tdCount = $(this).children('td').length;
                if (tdCount !== headerCount) {
                    console.warn('Row', idx, 'has', tdCount, 'tds (expected', headerCount + ')');
                    $(this).css('outline', '2px solid red');
                    console.log('Problematic row HTML:', $(this).prop('outerHTML'));
                } else {
                    console.log('Row', idx, 'OK (', tdCount, 'tds)');
                }
            });
        } catch (e) {
            console.error('Error checking table columns', e);
        }

        // Initialize DataTable with explicit columns to avoid incorrect column count warning
        // Try load Indonesian language file from CDN; if it fails, fall back to embedded translations
        $('#leaveTable').DataTable({
            order: [[0, 'desc']]
        });
    });
    
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
