<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Master Departemen</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#departmentModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Departemen
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="departmentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Departemen</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Jumlah Pegawai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $i => $dept): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($dept['code']) ?></strong></td>
                        <td><?= esc($dept['name']) ?></td>
                        <td><?= esc($dept['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($dept['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary"><?= $dept['employee_count'] ?? 0 ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editItem(<?= json_encode($dept) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('master/departments/' . $dept['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data departemen</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/departments/store') ?>" method="post" id="itemForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="itemId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control" required maxlength="10">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
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
        $('#departmentsTable').DataTable();
    });
    
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('itemForm').action = '<?= base_url('master/departments/store') ?>';
        document.getElementById('itemId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Departemen';
        document.getElementById('is_active').checked = true;
    }
    
    function editItem(item) {
        document.getElementById('itemForm').action = '<?= base_url('master/departments/update') ?>/' + item.id;
        document.getElementById('itemId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('name').value = item.name;
        document.getElementById('description').value = item.description || '';
        document.getElementById('is_active').checked = item.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Departemen';
        
        new bootstrap.Modal(document.getElementById('departmentModal')).show();
    }
</script>
<?= $this->endSection() ?>
