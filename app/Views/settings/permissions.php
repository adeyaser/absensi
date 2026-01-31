<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Pengaturan Permission</h1>
</div>

<div class="card">

    <div class="card-body">
        <form action="<?= base_url('settings/save-permissions') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= $selectedGroup ?>">
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Atur hak akses untuk grup: <strong><?= $groups[array_search($selectedGroup, array_column($groups, 'id'))]['name'] ?? 'Unknown' ?></strong>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Menu</th>
                            <th class="text-center" width="100">Lihat</th>
                            <th class="text-center" width="100">Tambah</th>
                            <th class="text-center" width="100">Edit</th>
                            <th class="text-center" width="100">Hapus</th>
                            <th class="text-center" width="100">Semua</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td>
                                <?php if ($menu['parent_id'] != 0): ?>
                                <span class="ms-4 text-muted"><i class="fas fa-level-up-alt fa-rotate-90 me-2"></i></span>
                                <?php endif; ?>
                                <i class="<?= $menu['icon'] ?> me-2"></i> <?= esc($menu['title']) ?>
                            </td>
                            <?php 
                                $p = $permissions[$menu['id']] ?? null; 
                                $actions = ['can_view', 'can_create', 'can_edit', 'can_delete'];
                            ?>
                            <?php foreach ($actions as $action): ?>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input type="checkbox" class="form-check-input perm-checkbox action-<?= $action ?>"
                                           name="permissions[<?= $menu['id'] ?>][<?= $action ?>]"
                                           value="1" data-menu="<?= $menu['id'] ?>"
                                           <?= ($p && $p[$action]) ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input type="checkbox" class="form-check-input select-all-row"
                                           data-menu="<?= $menu['id'] ?>">
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Simpan Hak Akses
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Select all for a menu row
    document.querySelectorAll('.select-all-row').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const menu = this.dataset.menu;
            const checked = this.checked;
            
            document.querySelectorAll('.perm-checkbox[data-menu="' + menu + '"]')
                .forEach(function(cb) { cb.checked = checked; });
        });
    });
</script>
<?= $this->endSection() ?>
