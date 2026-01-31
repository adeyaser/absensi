<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1>Master Jabatan</h1>
    <a href="<?= base_url('master/positions/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Tambah Jabatan
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="positionsTable">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Jabatan</th>
                        <th>Departemen</th>
                        <th width="80" class="text-center">Level</th>
                        <th class="text-end">Gaji Pokok</th>
                        <th class="text-center">Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($positions)): ?>
                    <?php foreach ($positions as $i => $pos): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($pos['code']) ?></strong></td>
                        <td><?= esc($pos['name']) ?></td>
                        <td><?= esc($pos['department_name'] ?? '-') ?></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $pos['level'] ?></span></td>
                        <td class="text-end fw-bold">Rp <?= number_format($pos['base_salary'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php if ($pos['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('master/positions/edit/' . $pos['id']) ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= base_url('master/position/' . $pos['id']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data jabatan</td>
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
        $('#positionsTable').DataTable();
    });

    function confirmDelete(url) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data jabatan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Use fetch for DELETE request
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Terhapus!', data.message, 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Terjadi kesalahan!', 'Tidak dapat menghapus data.', 'error');
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
