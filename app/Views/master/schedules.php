<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Jadwal Kerja</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Jadwal
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="schedulesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jadwal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Jam Istirahat</th>
                        <th>Toleransi</th>
                        <th>Total Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $i => $sch): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($sch['name']) ?></strong></td>
                        <td><span class="badge bg-success"><?= $sch['clock_in'] ?></span></td>
                        <td><span class="badge bg-danger"><?= $sch['clock_out'] ?></span></td>
                        <td><?= $sch['break_start'] ?? '-' ?> - <?= $sch['break_end'] ?? '-' ?></td>
                        <td><?= $sch['late_tolerance'] ?> menit</td>
                        <td><?= $sch['work_hours'] ?? 8 ?> jam</td>
                        <td>
                            <?php if ($sch['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editItem(<?= json_encode($sch) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('master/schedules/' . $sch['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data jadwal</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/schedules/store') ?>" method="post" id="itemForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="itemId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Jadwal <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                            <input type="time" name="clock_in" id="clock_in" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                            <input type="time" name="clock_out" id="clock_out" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai Istirahat</label>
                            <input type="time" name="break_start" id="break_start" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai Istirahat</label>
                            <input type="time" name="break_end" id="break_end" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Toleransi Terlambat (menit)</label>
                            <input type="number" name="late_tolerance" id="late_tolerance" class="form-control" value="15" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Jam Kerja</label>
                            <input type="number" name="work_hours" id="work_hours" class="form-control" value="8" step="0.5">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hari Kerja</label>
                        <div class="btn-group d-flex flex-wrap" role="group">
                            <?php $days = ['monday' => 'Sen', 'tuesday' => 'Sel', 'wednesday' => 'Rab', 'thursday' => 'Kam', 'friday' => 'Jum', 'saturday' => 'Sab', 'sunday' => 'Min']; ?>
                            <?php foreach ($days as $day => $label): ?>
                            <input type="checkbox" class="btn-check" name="working_days[]" value="<?= $day ?>" id="day_<?= $day ?>" <?= in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="day_<?= $day ?>"><?= $label ?></label>
                            <?php endforeach; ?>
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
        $('#schedulesTable').DataTable();
    });
    
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('itemForm').action = '<?= base_url('master/schedules/store') ?>';
        document.getElementById('itemId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Jadwal';
        document.getElementById('is_active').checked = true;
        
        // Reset working days
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
            document.getElementById('day_' + day).checked = true;
        });
        ['saturday', 'sunday'].forEach(day => {
            document.getElementById('day_' + day).checked = false;
        });
    }
    
    function editItem(item) {
        document.getElementById('itemForm').action = '<?= base_url('master/schedules/update') ?>/' + item.id;
        document.getElementById('itemId').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('clock_in').value = item.clock_in;
        document.getElementById('clock_out').value = item.clock_out;
        document.getElementById('break_start').value = item.break_start || '';
        document.getElementById('break_end').value = item.break_end || '';
        document.getElementById('late_tolerance').value = item.late_tolerance;
        document.getElementById('work_hours').value = item.work_hours;
        document.getElementById('is_active').checked = item.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Jadwal';
        
        // Set working days
        const workingDays = item.working_days ? item.working_days.split(',') : [];
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
            document.getElementById('day_' + day).checked = workingDays.includes(day);
        });
        
        new bootstrap.Modal(document.getElementById('scheduleModal')).show();
    }
</script>
<?= $this->endSection() ?>
