<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Manajemen Group</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#groupModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Group
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Group</th>
                        <th>Deskripsi</th>
                        <th>Jumlah User</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $i => $group): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($group['name']) ?></strong></td>
                        <td><?= esc($group['description'] ?? '-') ?></td>
                        <td><span class="badge bg-primary"><?= $group['user_count'] ?? 0 ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="managePermissions(<?= $group['id'] ?>, '<?= esc($group['name']) ?>')">
                                <i class="fas fa-key"></i> Hak Akses
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editGroup(<?= htmlspecialchars(json_encode($group)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($group['id'] > 5): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('settings/groups/' . $group['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data group</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Group Modal -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('settings/groups/store') ?>" method="post" id="groupForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="groupId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="groupModalTitle">Tambah Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Group <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="groupName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="groupDescription" class="form-control" rows="3"></textarea>
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

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post" id="permissionsForm">
                <?= csrf_field() ?>
                
                <div class="modal-header">
                    <h5 class="modal-title">Hak Akses - <span id="groupNameTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="permissionsList">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan Hak Akses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function resetForm() {
        document.getElementById('groupForm').reset();
        document.getElementById('groupForm').action = '<?= base_url('settings/groups/store') ?>';
        document.getElementById('groupId').value = '';
        document.getElementById('groupModalTitle').textContent = 'Tambah Group';
    }
    
    function editGroup(group) {
        document.getElementById('groupForm').action = '<?= base_url('settings/groups/update') ?>/' + group.id;
        document.getElementById('groupId').value = group.id;
        document.getElementById('groupName').value = group.name;
        document.getElementById('groupDescription').value = group.description || '';
        document.getElementById('groupModalTitle').textContent = 'Edit Group';
        
        new bootstrap.Modal(document.getElementById('groupModal')).show();
    }
    
    function managePermissions(groupId, groupName) {
        window.location.href = '<?= base_url('settings/groups/permissions') ?>/' + groupId;
    }
</script>
<?= $this->endSection() ?>
