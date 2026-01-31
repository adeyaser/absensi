<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Komponen Gaji</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#componentModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Komponen
    </button>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-plus-circle me-2"></i> Pendapatan (Earnings)
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Nilai Default</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($earnings ?? [] as $comp): ?>
                        <tr>
                            <td>
                                <strong><?= esc($comp['name']) ?></strong>
                                <br><small class="text-muted"><?= esc($comp['code']) ?></small>
                            </td>
                            <td>
                                <?php if ($comp['calculation_type'] === 'fixed'): ?>
                                <span class="badge bg-primary">Fixed</span>
                                <?php elseif ($comp['calculation_type'] === 'percentage'): ?>
                                <span class="badge bg-info">Persentase</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Formula</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($comp['calculation_type'] === 'percentage'): ?>
                                <?= $comp['default_value'] ?>%
                                <?php elseif ($comp['calculation_type'] === 'fixed'): ?>
                                Rp <?= number_format($comp['default_value'], 0, ',', '.') ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editComponent(<?= json_encode($comp) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('payroll/components/' . $comp['id']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($earnings)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-minus-circle me-2"></i> Potongan (Deductions)
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Nilai Default</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deductions ?? [] as $comp): ?>
                        <tr>
                            <td>
                                <strong><?= esc($comp['name']) ?></strong>
                                <br><small class="text-muted"><?= esc($comp['code']) ?></small>
                            </td>
                            <td>
                                <?php if ($comp['calculation_type'] === 'fixed'): ?>
                                <span class="badge bg-primary">Fixed</span>
                                <?php elseif ($comp['calculation_type'] === 'percentage'): ?>
                                <span class="badge bg-info">Persentase</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Formula</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($comp['calculation_type'] === 'percentage'): ?>
                                <?= $comp['default_value'] ?>%
                                <?php elseif ($comp['calculation_type'] === 'fixed'): ?>
                                Rp <?= number_format($comp['default_value'], 0, ',', '.') ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editComponent(<?= json_encode($comp) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('payroll/components/' . $comp['id']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($deductions)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="componentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('payroll/components/store') ?>" method="post" id="componentForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="compId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Komponen</h5>
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
                            <input type="text" name="code" id="code" class="form-control" required maxlength="20">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="earning">Pendapatan</option>
                                <option value="deduction">Potongan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perhitungan</label>
                            <select name="calculation_type" id="calculation_type" class="form-select" required>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Persentase</option>
                                <option value="formula">Formula</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="defaultValueDiv">
                        <label class="form-label">Nilai Default</label>
                        <input type="number" name="default_value" id="default_value" class="form-control" step="0.01">
                        <small class="text-muted" id="valueHelp">Nilai tetap dalam Rupiah</small>
                    </div>
                    
                    <div class="mb-3" id="formulaDiv" style="display: none;">
                        <label class="form-label">Formula</label>
                        <textarea name="formula" id="formula" class="form-control" rows="2"></textarea>
                        <small class="text-muted">Variabel: BASE_SALARY, GROSS_SALARY, PRESENT_DAYS, WORK_DAYS</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_taxable" id="is_taxable" value="1" checked>
                                <label class="form-check-label" for="is_taxable">Kena Pajak</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
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
    document.getElementById('calculation_type').addEventListener('change', function() {
        const formulaDiv = document.getElementById('formulaDiv');
        const defaultValueDiv = document.getElementById('defaultValueDiv');
        const valueHelp = document.getElementById('valueHelp');
        
        if (this.value === 'formula') {
            formulaDiv.style.display = 'block';
            defaultValueDiv.style.display = 'none';
        } else {
            formulaDiv.style.display = 'none';
            defaultValueDiv.style.display = 'block';
            valueHelp.textContent = this.value === 'percentage' ? 'Dalam persen (%)' : 'Nilai tetap dalam Rupiah';
        }
    });
    
    function resetForm() {
        document.getElementById('componentForm').reset();
        document.getElementById('componentForm').action = '<?= base_url('payroll/components/store') ?>';
        document.getElementById('compId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Komponen';
        document.getElementById('formulaDiv').style.display = 'none';
        document.getElementById('defaultValueDiv').style.display = 'block';
    }
    
    function editComponent(comp) {
        document.getElementById('componentForm').action = '<?= base_url('payroll/components/update') ?>/' + comp.id;
        document.getElementById('compId').value = comp.id;
        document.getElementById('name').value = comp.name;
        document.getElementById('code').value = comp.code;
        document.getElementById('type').value = comp.type;
        document.getElementById('calculation_type').value = comp.calculation_type;
        document.getElementById('default_value').value = comp.default_value;
        document.getElementById('formula').value = comp.formula || '';
        document.getElementById('is_taxable').checked = comp.is_taxable == 1;
        document.getElementById('is_active').checked = comp.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Komponen';
        
        // Toggle formula/value visibility
        const formulaDiv = document.getElementById('formulaDiv');
        const defaultValueDiv = document.getElementById('defaultValueDiv');
        if (comp.calculation_type === 'formula') {
            formulaDiv.style.display = 'block';
            defaultValueDiv.style.display = 'none';
        } else {
            formulaDiv.style.display = 'none';
            defaultValueDiv.style.display = 'block';
        }
        
        new bootstrap.Modal(document.getElementById('componentModal')).show();
    }
</script>
<?= $this->endSection() ?>
