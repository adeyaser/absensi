<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Persetujuan Cuti</h1>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Departemen</label>
                <select name="department_id" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>><?= esc($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="leaveApprovalTable">
                <thead>
                    <tr>
                        <th>Tgl. Pengajuan</th>
                        <th>Pegawai</th>
                        <th>Departemen</th>
                        <th>Jenis Cuti</th>
                        <th>Tanggal Cuti</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leaves)): ?>
                    <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($leave['created_at'])) ?></td>
                        <td>
                            <strong><?= esc($leave['employee_name']) ?></strong>
                            <br><small class="text-muted"><?= esc($leave['employee_code']) ?></small>
                        </td>
                        <td><?= esc($leave['department_name'] ?? '-') ?></td>
                        <td><?= esc($leave['leave_type_name']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($leave['start_date'])) ?>
                            <?php if ($leave['start_date'] !== $leave['end_date']): ?>
                            - <?= date('d/m/Y', strtotime($leave['end_date'])) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $leave['total_days'] ?> hari</td>
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
                            <button class="btn btn-sm btn-info" onclick="showDetail(<?= htmlspecialchars(json_encode($leave)) ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($leave['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-success" onclick="approveLeave(<?= $leave['id'] ?>)">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectLeave(<?= $leave['id'] ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada pengajuan cuti</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (empty($leaves)): ?>
            <div class="py-3 text-center text-muted">Tidak ada pengajuan cuti</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengajuan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="150">Pegawai</td>
                        <td>: <strong id="detailEmployee"></strong></td>
                    </tr>
                    <tr>
                        <td>Jenis Cuti</td>
                        <td>: <span id="detailLeaveType"></span></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <span id="detailDates"></span></td>
                    </tr>
                    <tr>
                        <td>Durasi</td>
                        <td>: <span id="detailDuration"></span> hari</td>
                    </tr>
                    <tr>
                        <td>Alasan</td>
                        <td>: <span id="detailReason"></span></td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>: <span id="detailAttachment"></span></td>
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
        // Debugging: check header vs row columns
        try {
            const headerCount = $('#leaveApprovalTable thead tr').first().children('th').length;
            console.log('leaveApprovalTable header columns:', headerCount);
            $('#leaveApprovalTable tbody tr').each(function(idx) {
                const tdCount = $(this).children('td').length;
                if (tdCount !== headerCount) {
                    console.warn('Row', idx, 'has', tdCount, 'tds (expected', headerCount + ')');
                    $(this).css('outline', '2px solid red');
                    console.log('Problematic row HTML:', $(this).prop('outerHTML'));
                }
            });
        } catch (e) {
            console.error('Error checking table columns', e);
        }

        // Load language JSON with fallback and initialize DataTable with explicit columns
        const languageUrl = '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json';
        const fallbackLang = {
            "sEmptyTable": "Tidak ada data yang tersedia pada tabel",
            "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "sLengthMenu": "Tampilkan _MENU_ entri",
            "sLoadingRecords": "Memuat...",
            "sProcessing": "Memproses...",
            "sSearch": "Cari:",
            "oPaginate": {
                "sFirst": "Pertama",
                "sLast": "Terakhir",
                "sNext": "Berikut",
                "sPrevious": "Sebelumnya"
            }
        };

        $.getJSON(languageUrl)
            .done(function(lang) {
                initApprovalTable(lang);
            })
            .fail(function() {
                console.warn('Failed to load DataTables language file, using fallback translations');
                initApprovalTable(fallbackLang);
            });

        function initApprovalTable(lang) {
            // Normalize rows to match header column count to avoid DataTables warnings
            try {
                const headerCount = $('#leaveApprovalTable thead tr').first().children('th').length;
                $('#leaveApprovalTable tbody tr').each(function(idx) {
                    let $cols = $(this).children('td');
                    let tdCount = $cols.length;
                    if (tdCount < headerCount) {
                        // append empty tds
                        for (let i = tdCount; i < headerCount; i++) {
                            $(this).append('<td></td>');
                        }
                        console.warn('Padded row', idx, 'from', tdCount, 'to', headerCount);
                    } else if (tdCount > headerCount) {
                        // merge extra tds into the last expected td
                        let lastCell = $cols.eq(headerCount - 1);
                        let extraHtml = '';
                        $cols.slice(headerCount).each(function() {
                            extraHtml += '<div class="extra-td">' + $(this).html() + '</div>';
                            $(this).remove();
                        });
                        lastCell.append(extraHtml);
                        console.warn('Merged extra cells in row', idx, 'now', lastCell.html());
                    }
                });
            } catch (e) {
                console.error('Normalization error', e);
            }

            $('#leaveApprovalTable').DataTable({
                destroy: true,
                language: lang,
                order: [[0, 'desc']],
                columns: [
                    { name: 'created_at' },
                    { name: 'employee' },
                    { name: 'department' },
                    { name: 'leave_type' },
                    { name: 'date' },
                    { name: 'duration' },
                    { name: 'status' },
                    { name: 'actions', orderable: false, searchable: false }
                ]
            });
        }
    });
    
    function showDetail(leave) {
        document.getElementById('detailEmployee').textContent = leave.employee_name;
        document.getElementById('detailLeaveType').textContent = leave.leave_type_name;
        document.getElementById('detailDates').textContent = leave.start_date + ' s/d ' + leave.end_date;
        document.getElementById('detailDuration').textContent = leave.total_days;
        document.getElementById('detailReason').textContent = leave.reason;
        
        const attachment = document.getElementById('detailAttachment');
        if (leave.attachment) {
            attachment.innerHTML = '<a href="<?= base_url('writable/uploads/') ?>' + leave.attachment + '" target="_blank">Lihat Lampiran</a>';
        } else {
            attachment.textContent = '-';
        }
        
        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }
    
    function approveLeave(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: 'Anda yakin ingin menyetujui pengajuan cuti ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('leave/approve') ?>/' + id,
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
    
    function rejectLeave(id) {
        Swal.fire({
            title: 'Tolak Pengajuan?',
            input: 'textarea',
            inputLabel: 'Alasan Penolakan',
            inputPlaceholder: 'Masukkan alasan penolakan...',
            showCancelButton: true,
            confirmButtonText: 'Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('leave/reject') ?>/' + id,
                    method: 'POST',
                    data: { rejection_reason: result.value },
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
