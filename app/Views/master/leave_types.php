<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Jenis Cuti</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveTypeModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Jenis Cuti
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="leaveTypesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Quota/Tahun</th>
                        <th>Max Hari</th>
                        <th>Potong Gaji</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leaveTypes)): ?>
                    <?php foreach ($leaveTypes as $i => $type): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($type['name']) ?></strong></td>
                        <td><span class="badge bg-primary"><?= esc($type['code']) ?></span></td>
                        <td><?= $type['quota_per_year'] ?? ($type['quota'] ?? '-') ?> hari</td>
                        <td><?= $type['max_days'] ?? ($type['quota_per_year'] ?? ($type['quota'] ?? '-')) ?> hari</td>
                        <td>
                            <?php if ($type['is_paid']): ?>
                            <span class="badge bg-success">Tidak</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Ya</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($type['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editItem(<?= json_encode($type) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('master/leave-types/' . $type['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data jenis cuti</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="leaveTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/leave-types/store') ?>" method="post" id="itemForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="itemId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Jenis Cuti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control" required maxlength="10">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quota per Tahun</label>
                            <input type="number" name="quota_per_year" id="quota_per_year" class="form-control" min="0">
                            <small class="text-muted">Kosongkan jika tidak terbatas</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Hari Pengajuan</label>
                            <input type="number" name="max_days" id="max_days" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" checked>
                                <label class="form-check-label" for="is_paid">Cuti Berbayar</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_attachment" id="requires_attachment" value="1">
                                <label class="form-check-label" for="requires_attachment">Wajib Lampiran</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan
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
        $('#leaveTypesTable').DataTable();
    });
    
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('itemForm').action = '<?= base_url('master/leave-types/store') ?>';
        document.getElementById('itemId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Jenis Cuti';
        document.getElementById('is_active').checked = true;
        document.getElementById('is_paid').checked = true;
    }
    
    function editItem(item) {
        document.getElementById('itemForm').action = '<?= base_url('master/leave-types/update') ?>/' + item.id;
        document.getElementById('itemId').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('code').value = item.code;
        document.getElementById('quota_per_year').value = item.quota_per_year || '';
        document.getElementById('max_days').value = (item.max_days !== undefined && item.max_days !== null) ? item.max_days : (item.quota_per_year || 1);
        document.getElementById('description').value = item.description || '';
        document.getElementById('is_paid').checked = item.is_paid == 1;
        document.getElementById('requires_attachment').checked = item.requires_attachment == 1;
        document.getElementById('is_active').checked = item.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Jenis Cuti';
        
        new bootstrap.Modal(document.getElementById('leaveTypeModal')).show();
    }
</script>
<?= $this->endSection() ?>
