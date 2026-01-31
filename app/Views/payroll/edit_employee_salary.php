<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('payroll/employee-salary') ?>">Gaji Pegawai</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Atur Gaji</li>
                    </ol>
                </nav>
                <h1>Atur Gaji: <?= esc($emp['full_name']) ?></h1>
                <p class="text-muted small mb-0"><?= esc($emp['department_name']) ?> | <?= esc($emp['position_name']) ?></p>
            </div>
            <div>
                <a href="<?= base_url('master/positions') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Kelola di Jabatan
                </a>
                <a href="<?= base_url('payroll/employee-salary') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?= base_url('payroll/update-employee-salary') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">

                    <div class="alert alert-info py-2 mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Data gaji & tunjangan di bawah ini diambil berdasarkan <strong>Jabatan</strong> karyawan. 
                        Untuk mengubah nilai ini, silakan gunakan menu <strong>Master Jabatan</strong>.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="base_salary" id="base_salary" class="form-control" 
                                       value="<?= $emp['base_salary'] ?>" readonly>
                                <span class="input-group-text bg-light text-muted small"><?= ($emp['is_inherited_salary'] ?? false) ? 'Jabatan' : 'Khusus' ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Efektif Mulai</label>
                            <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-12">
                            <h6 class="mb-3 border-bottom pb-2">
                                <i class="fas fa-list-ul me-2 text-primary"></i>Komponen Gaji Aktif
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nama Komponen</th>
                                            <th>Kategori</th>
                                            <th class="text-end" style="width: 250px;">Jumlah</th>
                                            <th class="text-center" style="width: 120px;">Sumber</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $hasComponents = false;
                                        $allGlobalComps = array_merge($earningComponents, $deductionComponents);
                                        
                                        foreach ($allGlobalComps as $comp):
                                            if ($comp['code'] === 'BASIC') continue;

                                            $amount = 0;
                                            $source = '';
                                            $isEnabled = false;

                                            // Position check
                                            if (!empty($emp['position_components'])) {
                                                foreach ($emp['position_components'] as $pc) {
                                                    if ($pc['component_id'] == $comp['id']) {
                                                        $amount = $pc['amount'];
                                                        $source = 'Jabatan';
                                                        $isEnabled = true;
                                                        break;
                                                    }
                                                }
                                            }

                                            // Employee check (override)
                                            if (!empty($emp['salary_components'])) {
                                                foreach ($emp['salary_components'] as $ec) {
                                                    if ($ec['component_id'] == $comp['id']) {
                                                        $amount = $ec['value'];
                                                        $source = 'Khusus';
                                                        $isEnabled = true;
                                                        break;
                                                    }
                                                }
                                            }

                                            if ($isEnabled):
                                                $hasComponents = true;
                                        ?>
                                        <tr>
                                            <td>
                                                <div><?= esc($comp['name']) ?></div>
                                                <small class="text-muted"><?= esc($comp['code']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $comp['type'] == 'earning' ? 'success' : 'danger' ?>-soft text-<?= $comp['type'] == 'earning' ? 'success' : 'danger' ?> px-3">
                                                    <?= $comp['type'] == 'earning' ? 'Pendapatan' : 'Potongan' ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold font-monospace">
                                                Rp <?= number_format($amount, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-2"><?= $source ?></span>
                                            </td>
                                        </tr>
                                        <?php 
                                            endif;
                                        endforeach; 

                                        if (!$hasComponents):
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fas fa-exclamation-circle fa-2x mb-3 d-block"></i>
                                                Belum ada komponen gaji tambahan untuk jabatan ini.
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-university me-2 text-primary"></i>Informasi Rekening Bank</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bank_name" id="bank_name" class="form-select">
                                        <option value="">-- Pilih Bank --</option>
                                        <?php 
                                        $banks = ['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Danamon', 'Permata', 'BSI'];
                                        foreach($banks as $bank): 
                                        ?>
                                        <option value="<?= $bank ?>" <?= ($emp['bank_name'] ?? '') == $bank ? 'selected' : '' ?>><?= $bank ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" name="bank_account" class="form-control" value="<?= esc($emp['bank_account'] ?? '') ?>" placeholder="Misal: 1234567890">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Atas Nama</label>
                                    <input type="text" name="bank_holder" class="form-control" value="<?= esc($emp['bank_holder'] ?? $emp['full_name']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= base_url('payroll/employee-salary') ?>" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Simpan Informasi Bank
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .badge-soft { border: none; font-weight: 600; }
</style>
<?= $this->endSection() ?>
