<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Hari Libur</h1>
    <div>
        <button class="btn btn-success me-2" onclick="syncHolidays()">
            <i class="fas fa-sync me-2"></i> Sync Nasional
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i> Tambah Hari Libur
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="holidaysTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Nasional</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($holidays)): ?>
                            <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td>
                                    <strong><?= date('d M Y', strtotime($holiday['date'])) ?></strong>
                                    <br><small class="text-muted"><?= date('l', strtotime($holiday['date'])) ?></small>
                                </td>
                                <td><?= esc($holiday['name']) ?></td>
                                <td><?= esc($holiday['description'] ?? '-') ?></td>
                                <td>
                                    <?php if ($holiday['is_national']): ?>
                                    <span class="badge bg-success">Ya</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Tidak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editItem(<?= json_encode($holiday) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('master/holidays/' . $holiday['id']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data hari libur</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar me-2"></i> Hari Libur Mendatang
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php 
                    $upcomingHolidays = array_filter($holidays ?? [], function($h) {
                        return strtotime($h['date']) >= strtotime('today');
                    });
                    usort($upcomingHolidays, function($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });
                    $upcomingHolidays = array_slice($upcomingHolidays, 0, 5);
                    ?>
                    <?php if (!empty($upcomingHolidays)): ?>
                    <?php foreach ($upcomingHolidays as $holiday): ?>
                    <li class="list-group-item">
                        <strong><?= esc($holiday['name']) ?></strong>
                        <br><small class="text-muted"><?= date('d F Y', strtotime($holiday['date'])) ?></small>
                    </li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li class="list-group-item text-muted">Tidak ada hari libur mendatang</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/holidays/store') ?>" method="post" id="itemForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="itemId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Hari Libur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_national" id="is_national" value="1" checked>
                        <label class="form-check-label" for="is_national">Hari Libur Nasional</label>
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
        $('#holidaysTable').DataTable({
            order: [[0, 'asc']]
        });
    });
    
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('itemForm').action = '<?= base_url('master/holidays/store') ?>';
        document.getElementById('itemId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Hari Libur';
        document.getElementById('is_national').checked = true;
    }
    
    function syncHolidays() {
        Swal.fire({
            title: 'Sinkronisasi Hari Libur?',
            text: 'Ini akan mengambil data hari libur nasional tahun ini dari server eksternal.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sinkronisasi',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('<?= base_url('master/holidays/sync') ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Gagal sinkronisasi');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.value.message,
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    function editItem(item) {
        document.getElementById('itemForm').action = '<?= base_url('master/holidays/update') ?>/' + item.id;
        document.getElementById('itemId').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('date').value = item.date;
        document.getElementById('description').value = item.description || '';
        document.getElementById('is_national').checked = item.is_national == 1;
        document.getElementById('modalTitle').textContent = 'Edit Hari Libur';
        
        new bootstrap.Modal(document.getElementById('holidayModal')).show();
    }
</script>
<?= $this->endSection() ?>
