<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Manajemen User</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah User
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Nama</th>
                        <th>Group</th>
                        <th>Password Hint</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $i => $user): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($user['username']) ?></strong></td>
                        <td><?= esc($user['email']) ?></td>
                        <td><?= esc($user['full_name'] ?? '-') ?></td>
                        <td><span class="badge bg-primary"><?= esc($user['group_name']) ?></span></td>
                        <td><small class="text-muted"><?= esc($user['password_hint'] ?? '-') ?></small></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($user['id'] != session('user_id')): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('settings/user/' . $user['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data user</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('settings/save-user') ?>" method="post" id="userForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="userId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="text-muted" id="passwordNote">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password Hint (Bantuan Pengingat)</label>
                        <input type="text" name="password_hint" id="password_hint" class="form-control" placeholder="Contoh: Nama hewan peliharaan">
                        <small class="text-muted">Opsional: Catatan untuk membantu admin mengingat password aslinya</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Group <span class="text-danger">*</span></label>
                        <select name="group_id" id="group_id" class="form-select" required>
                            <option value="">-- Pilih Group --</option>
                            <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= esc($group['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kaitkan dengan Pegawai</label>
                        <select name="employee_id" id="employee_id" class="form-select select2">
                            <option value="">-- Tidak dikaitkan --</option>
                            <?php foreach ($employees ?? [] as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= esc($emp['employee_code'] . ' - ' . $emp['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">User Aktif</label>
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
        $('#usersTable').DataTable();
        
        $('#employee_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#userModal')
        });
    });
    
    function resetForm() {
        document.getElementById('userForm').reset();
        document.getElementById('userForm').action = '<?= base_url('settings/save-user') ?>';
        document.getElementById('userId').value = '';
        document.getElementById('userModalTitle').textContent = 'Tambah User';
        document.getElementById('password_hint').value = '';
        document.getElementById('password').required = true;
        document.getElementById('passwordRequired').style.display = '';
        document.getElementById('passwordNote').textContent = 'Minimal 6 karakter';
    }
    
    function editUser(user) {
        // Use same save endpoint; controller decides update vs insert based on hidden id
        document.getElementById('userForm').action = '<?= base_url('settings/save-user') ?>';
        document.getElementById('userId').value = user.id;
        document.getElementById('username').value = user.username;
        document.getElementById('email').value = user.email;
        document.getElementById('group_id').value = user.group_id;
        document.getElementById('employee_id').value = user.employee_id || '';
        document.getElementById('is_active').checked = user.is_active == 1;
        document.getElementById('password_hint').value = user.password_hint || '';
        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('password').required = false;
        document.getElementById('passwordRequired').style.display = 'none';
        document.getElementById('passwordNote').textContent = 'Kosongkan jika tidak ingin mengubah password';
        
        $('#employee_id').val(user.employee_id || '').trigger('change');
        
        new bootstrap.Modal(document.getElementById('userModal')).show();
    }

    // Submit user form via AJAX
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const data = form.serialize();

        showLoading();

        $.post(url, data)
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showToast('success', response.message);
                    $('#userModal').modal('hide');
                    // Redirect to users list after success
                    setTimeout(function() { window.location.href = '<?= base_url('settings/users') ?>'; }, 700);
                } else {
                    showToast('error', response.message || 'Terjadi kesalahan');
                }
            })
            .fail(function(xhr) {
                hideLoading();
                let msg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showToast('error', msg);
            });
    });
</script>
<?= $this->endSection() ?>
