<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Menu Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Menu
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="menusTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Menu</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>Parent</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($menus)): ?>
                    <?php foreach ($menus as $i => $menu): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <?php if ($menu['parent_id']): ?>
                            <span class="text-muted">├─</span>
                            <?php endif; ?>
                            <i class="<?= esc($menu['icon']) ?> me-2"></i>
                            <strong><?= esc($menu['name']) ?></strong>
                        </td>
                        <td><code><?= esc($menu['url']) ?></code></td>
                        <td><i class="<?= esc($menu['icon']) ?>"></i> <?= esc($menu['icon']) ?></td>
                        <td><?= esc($menu['parent_name'] ?? '-') ?></td>
                        <td><?= $menu['sort_order'] ?></td>
                        <td>
                            <?php if ($menu['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editMenu(<?= json_encode($menu) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('settings/menus/' . $menu['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data menu</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="menuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('settings/menus/store') ?>" method="post" id="menuForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="menuId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Parent Menu</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">-- Tidak Ada (Root) --</option>
                            <?php foreach ($parentMenus ?? [] as $parent): ?>
                            <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="text" name="url" id="url" class="form-control" placeholder="dashboard">
                        <small class="text-muted">URL tanpa leading slash (/) dan base URL</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Icon <span class="text-danger">*</span></label>
                            <input type="text" name="icon" id="icon" class="form-control" placeholder="fas fa-home" required>
                            <small class="text-muted">
                                Gunakan class Font Awesome. 
                                <a href="https://fontawesome.com/icons" target="_blank">Lihat daftar icon</a>
                            </small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permission Key</label>
                        <input type="text" name="permission_key" id="permission_key" class="form-control" placeholder="module.action">
                        <small class="text-muted">Kosongkan jika menu bisa diakses semua role</small>
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
        $('#menusTable').DataTable({
            order: [[5, 'asc']]
        });
    });
    
    function resetForm() {
        document.getElementById('menuForm').reset();
        document.getElementById('menuForm').action = '<?= base_url('settings/menus/store') ?>';
        document.getElementById('menuId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Menu';
        document.getElementById('is_active').checked = true;
    }
    
    function editMenu(menu) {
        document.getElementById('menuForm').action = '<?= base_url('settings/menus/update') ?>/' + menu.id;
        document.getElementById('menuId').value = menu.id;
        document.getElementById('parent_id').value = menu.parent_id || '';
        document.getElementById('name').value = menu.name;
        document.getElementById('url').value = menu.url || '';
        document.getElementById('icon').value = menu.icon;
        document.getElementById('sort_order').value = menu.sort_order;
        document.getElementById('permission_key').value = menu.permission_key || '';
        document.getElementById('is_active').checked = menu.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Menu';
        
        new bootstrap.Modal(document.getElementById('menuModal')).show();
    }
</script>
<?= $this->endSection() ?>
