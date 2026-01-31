<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Pengajuan Lembur</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#overtimeModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Ajukan Lembur
    </button>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?= $stats['total'] ?? 0 ?></h4>
                <small>Total Pengajuan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h4><?= $stats['pending'] ?? 0 ?></h4>
                <small>Menunggu Persetujuan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?= $stats['approved'] ?? 0 ?></h4>
                <small>Disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?= number_format($stats['total_hours'] ?? 0, 1) ?></h4>
                <small>Total Jam Lembur</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="overtimeTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th width="100">Tanggal</th>
                        <th>Karyawan</th>
                        <th>Atasan</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Approver</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overtimes as $i => $ot): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= date('d/m/Y', strtotime($ot['date'])) ?></td>
                            <td>
                                <strong><?= esc($ot['employee_name']) ?></strong>
                                <br><small class="text-muted"><?= esc($ot['department_name'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($ot['employee_supervisor_name'])): ?>
                                    <small><?= esc($ot['employee_supervisor_name']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('H:i', strtotime($ot['start_time'])) ?></td>
                            <td><?= date('H:i', strtotime($ot['end_time'])) ?></td>
                            <td><span class="badge bg-primary"><?= $ot['duration_hours'] ?> jam</span></td>
                            <td><small title="<?= esc($ot['reason']) ?>"><?= esc(substr($ot['reason'], 0, 30)) ?><?= strlen($ot['reason']) > 30 ? '...' : '' ?></small></td>
                            <td>
                                <?php
                                $statusText = ['pending' => 'Menunggu Atasan', 'pending_finance' => 'Menunggu Keuangan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
                                $statusClass = ['pending' => 'warning', 'pending_finance' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
                                ?>
                                <span class="badge bg-<?= $statusClass[$ot['status']] ?? 'secondary' ?>">
                                    <?= $statusText[$ot['status']] ?? $ot['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $hasApprover = !empty($ot['supervisor_name']) || !empty($ot['finance_name']) || !empty($ot['designated_supervisor_name']) || !empty($ot['designated_finance_name']);
                                ?>
                                <?php if ($hasApprover): ?>
                                    <div style="font-size: 0.75rem;">
                                        <div>
                                            <i class="fas fa-user-tie text-primary me-1"></i>
                                            <?= esc($ot['supervisor_name'] ?: ($ot['designated_supervisor_name'] ?: '-')) ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-calculator text-success me-1"></i>
                                            <?= esc($ot['finance_name'] ?: ($ot['designated_finance_name'] ?: '-')) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (session('group_id') <= 2): ?>
                                    <?php if ($ot['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success p-1" onclick="approveOvertime(<?= $ot['id'] ?>, 'supervisor')" title="Setujui Atasan"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-sm btn-danger p-1" onclick="rejectOvertime(<?= $ot['id'] ?>)" title="Tolak"><i class="fas fa-times"></i></button>
                                    <?php elseif ($ot['status'] === 'pending_finance'): ?>
                                        <button class="btn btn-sm btn-primary p-1" onclick="approveOvertime(<?= $ot['id'] ?>, 'finance')" title="Setujui Keuangan"><i class="fas fa-check-double"></i></button>
                                        <button class="btn btn-sm btn-danger p-1" onclick="rejectOvertime(<?= $ot['id'] ?>)" title="Tolak"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                <?php elseif ($ot['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-secondary" onclick="cancelOvertime(<?= $ot['id'] ?>)">Batal</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="overtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('attendance/overtime/store') ?>" method="post" id="overtimeForm">
                <?= csrf_field() ?>
                
                <div class="modal-header">
                    <h5 class="modal-title">Pengajuan Lembur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (session('group_id') <= 2): ?>
                    <div class="mb-3">
                        <label class="form-label">Karyawan <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employeeSelect" class="form-select select2" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php foreach ($employees ?? [] as $emp): ?>
                            <option value="<?= $emp['id'] ?>" data-supervisor-user-id="<?= $emp['supervisor_user_id'] ?? '' ?>"><?= esc($emp['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Lembur <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required 
                                  placeholder="Jelaskan alasan dan pekerjaan yang dilakukan..."></textarea>
                    </div>
                    
                    <?php if (session('group_id') <= 2): ?>
                    <hr class="my-3">
                    <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Pilih approver untuk pengajuan ini</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-user-tie text-primary me-1"></i> Approver Atasan</label>
                            <select name="approver_supervisor_id" id="approver_supervisor_id" class="form-select select2">
                                <option value="">-- Pilih Atasan --</option>
                                <?php foreach ($approvers ?? [] as $approver): ?>
                                    <?php if (in_array($approver['group_id'], [1, 2, 3])): ?>
                                    <option value="<?= $approver['id'] ?>" data-group-id="<?= $approver['group_id'] ?>"><?= esc($approver['username']) ?> (<?= esc($approver['group_name'] ?? 'Admin') ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Yang akan menyetujui sebagai atasan</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-calculator text-success me-1"></i> Approver Keuangan</label>
                            <select name="approver_finance_id" id="approver_finance_id" class="form-select select2">
                                <option value="">-- Pilih Keuangan --</option>
                                <?php foreach ($approvers ?? [] as $approver): ?>
                                    <?php if (in_array($approver['group_id'], [1, 2])): ?>
                                    <option value="<?= $approver['id'] ?>" data-group-id="<?= $approver['group_id'] ?>"><?= esc($approver['username']) ?> (<?= esc($approver['group_name'] ?? 'Admin') ?>)</option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Yang akan menyetujui final</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i> Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#overtimeTable').DataTable({
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 10] }
            ]
        });
        
        $('.select2').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#overtimeModal')
        });
        
        // Auto-select Approver Atasan & Keuangan when employee is selected
        $('#employeeSelect').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var supervisorUserId = selectedOption.attr('data-supervisor-user-id'); // Use attr for more reliability
            
            console.log('Employee changed. Supervisor User ID:', supervisorUserId);
            
            // 1. Auto-select Atasan
            if (supervisorUserId && supervisorUserId !== '') {
                $('#approver_supervisor_id').val(supervisorUserId).trigger('change');
                console.log('Setting Approver Atasan to:', supervisorUserId);
            } else {
                $('#approver_supervisor_id').val('').trigger('change');
                console.log('Resetting Approver Atasan');
            }
            
            // 2. Auto-select Keuangan (Select first user from HR/Finance group - ID 2 or 1 if 2 doesn't exist)
            var financeSelect = $('#approver_finance_id');
            var firstFinance = financeSelect.find('option[data-group-id="2"]').first().val();
            if (!firstFinance) {
                firstFinance = financeSelect.find('option[data-group-id="1"]').first().val();
            }
            
            if (firstFinance) {
                financeSelect.val(firstFinance).trigger('change');
                console.log('Setting Approver Keuangan to:', firstFinance);
            }
        });
    });
    
    function resetForm() {
        document.getElementById('overtimeForm').reset();
    }
    
    function approveOvertime(id, stage) {
        let title = 'Setujui Lembur?';
        let text = 'Pengajuan lembur akan disetujui';
        
        if (stage === 'supervisor') {
            text = 'Setujui sebagai Atasan? Status akan berubah menjadi Menunggu Keuangan.';
        } else if (stage === 'finance') {
            text = 'Setujui sebagai Keuangan? Pengajuan akan disetujui sepenuhnya.';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Setujui'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('attendance/overtime/approve') ?>/' + id;
            }
        });
    }
    
    function rejectOvertime(id) {
        Swal.fire({
            title: 'Tolak Lembur?',
            input: 'textarea',
            inputLabel: 'Alasan penolakan',
            inputPlaceholder: 'Masukkan alasan...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Tolak'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('attendance/overtime/reject') ?>/' + id + '?reason=' + encodeURIComponent(result.value);
            }
        });
    }
    
    function cancelOvertime(id) {
        Swal.fire({
            title: 'Batalkan Pengajuan?',
            text: 'Pengajuan lembur akan dibatalkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Tidak',
            confirmButtonText: 'Ya, Batalkan'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('attendance/overtime/cancel') ?>/' + id;
            }
        });
    }
</script>
<?= $this->endSection() ?>
