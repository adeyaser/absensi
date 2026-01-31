<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Data Pegawai</h1>
    <a href="<?= base_url('employees/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Tambah Pegawai
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="employeesTable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Tgl Masuk</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $i => $emp): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($emp['employee_code']) ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($emp['photo']): ?>
                                <img src="<?= base_url('writable/uploads/' . $emp['photo']) ?>" alt="" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                                <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <?= strtoupper(substr($emp['full_name'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?= esc($emp['full_name']) ?></strong>
                                    <br><small class="text-muted"><?= esc($emp['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= esc($emp['department_name'] ?? '-') ?></td>
                        <td><?= esc($emp['position_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($emp['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($emp['join_date'])) ?></td>
                        <td>
                            <a href="<?= base_url('employees/' . $emp['id']) ?>" class="btn btn-sm btn-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('employees/edit/' . $emp['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('employees/' . $emp['id']) ?>')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data pegawai</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#employeesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[1, 'asc']]
        });
    });
</script>
<?= $this->endSection() ?>
