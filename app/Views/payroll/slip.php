<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Slip Gaji - <?= date('F Y', mktime(0, 0, 0, $payroll['period_month'], 1, $payroll['period_year'])) ?></h1>
    <div>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Cetak
        </button>
        <button class="btn btn-danger" onclick="downloadPdf()">
            <i class="fas fa-file-pdf me-2"></i> PDF
        </button>
        <a href="<?= base_url('payroll/my-slips') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

<div class="card" id="payslip">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h3 class="text-primary mb-0"><?= esc($settings['company_name'] ?? 'PT. Contoh') ?></h3>
                <p class="text-muted mb-0"><?= esc($settings['company_address'] ?? 'Alamat Perusahaan') ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h4 class="mb-1">SLIP GAJI</h4>
                <p class="text-muted mb-0">Periode: <?= date('F Y', mktime(0, 0, 0, $payroll['period_month'], 1, $payroll['period_year'])) ?></p>
            </div>
        </div>
        
        <hr>
        
        <!-- Employee Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150">Nama</td>
                        <td>: <strong><?= esc($payroll['full_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>NIK</td>
                        <td>: <?= esc($payroll['employee_code']) ?></td>
                    </tr>
                    <tr>
                        <td>Departemen</td>
                        <td>: <?= esc($payroll['department_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>: <?= esc($payroll['position_name'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150">Hari Kerja</td>
                        <td>: <?= $payroll['work_days'] ?> hari</td>
                    </tr>
                    <tr>
                        <td>Hadir</td>
                        <td>: <?= $payroll['present_days'] ?> hari</td>
                    </tr>
                    <tr>
                        <td>Terlambat</td>
                        <td>: <?= $payroll['late_days'] ?> hari (<?= $payroll['late_minutes'] ?> menit)</td>
                    </tr>
                    <tr>
                        <td>Cuti/Sakit</td>
                        <td>: <?= $payroll['leave_days'] + $payroll['sick_days'] ?> hari</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Earnings and Deductions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-header bg-success text-white">
                        <strong><i class="fas fa-plus-circle me-2"></i> PENDAPATAN</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td>Gaji Pokok</td>
                                    <td class="text-end"><?= number_format($payroll['base_salary'], 0, ',', '.') ?></td>
                                </tr>
                                <?php foreach ($earnings as $earning): ?>
                                <?php if ($earning['component_name'] !== 'Gaji Pokok'): ?>
                                <tr>
                                    <td><?= esc($earning['component_name']) ?></td>
                                    <td class="text-end"><?= number_format($earning['amount'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-success text-white">
                                <tr>
                                    <th>Total Pendapatan</th>
                                    <th class="text-end"><?= number_format($payroll['gross_salary'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-header bg-danger text-white">
                        <strong><i class="fas fa-minus-circle me-2"></i> POTONGAN</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <?php if (empty($deductions)): ?>
                                <tr>
                                    <td class="text-muted text-center" colspan="2">Tidak ada potongan</td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ($deductions as $deduction): ?>
                                <tr>
                                    <td><?= esc($deduction['component_name']) ?></td>
                                    <td class="text-end"><?= number_format($deduction['amount'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-danger text-white">
                                <tr>
                                    <th>Total Potongan</th>
                                    <th class="text-end"><?= number_format($payroll['total_deductions'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Net Salary -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card bg-primary text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">GAJI BERSIH (TAKE HOME PAY)</h4>
                        <h2 class="mb-0">Rp <?= number_format($payroll['net_salary'], 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Signature -->
        <div class="row mt-5">
            <div class="col-md-6">
                <p class="mb-0">Diterima oleh:</p>
                <div style="height: 80px;"></div>
                <p class="mb-0 border-top pt-2" style="width: 200px;">
                    <strong><?= esc($payroll['full_name']) ?></strong>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0"><?= esc($settings['company_city'] ?? 'Jakarta') ?>, <?= date('d F Y') ?></p>
                <p class="mb-0">HRD Manager</p>
                <div style="height: 80px;"></div>
                <p class="mb-0 border-top pt-2 d-inline-block" style="width: 200px;">
                    <strong>________________</strong>
                </p>
            </div>
        </div>
        
        <hr class="mt-5">
        
        <p class="text-muted text-center mb-0">
            <small>Slip gaji ini dibuat secara elektronik dan sah tanpa tanda tangan basah.</small>
        </p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    @media print {
        .sidebar, .navbar-main, .page-header > div, .btn {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0 !important;
        }
        
        .page-header {
            margin-bottom: 0 !important;
        }
        
        .page-header h1 {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
<?= $this->endSection() ?>

<style>
    .grayscale-print {
        filter: grayscale(100%);
    }
    .grayscale-print .card-header {
        background-color: #f0f0f0 !important; /* Force light gray background for headers */
        color: #000 !important; /* Force black text */
        border: 1px solid #000;
    }
    .grayscale-print .text-primary, 
    .grayscale-print .text-success, 
    .grayscale-print .text-danger,
    .grayscale-print .text-muted {
        color: #000 !important;
    }
    .grayscale-print .bg-primary,
    .grayscale-print .bg-success,
    .grayscale-print .bg-danger {
        background-color: #333 !important;
        color: #fff !important;
    }
    .grayscale-print .bg-light {
        background-color: #fff !important;
    }
</style>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPdf() {
        const element = document.getElementById('payslip');
        
        // Add grayscale class for black and white printing
        element.classList.add('grayscale-print');
        
        const opt = {
            margin: [10, 10, 10, 10], // top, left, bottom, right in mm
            // Filename: Nama PT - Periode - Nama Pegawai
            filename: '<?= esc($settings['company_name'] ?? 'PT. Contoh') ?> - <?= date('F Y', mktime(0, 0, 0, $payroll['period_month'], 1, $payroll['period_year'])) ?> - <?= esc($payroll['full_name']) ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Show loading state
        const btn = document.querySelector('button[onclick="downloadPdf()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';
        btn.disabled = true;

        html2pdf().set(opt).from(element).save().then(() => {
            // Restore button and remove grayscale class
            btn.innerHTML = originalText;
            btn.disabled = false;
            element.classList.remove('grayscale-print');
        }).catch(err => {
            console.error('Error generating PDF:', err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            element.classList.remove('grayscale-print');
            alert('Gagal membuat PDF. Silakan coba lagi atau gunakan tombol Cetak.');
        });
    }
</script>
<?= $this->endSection() ?>
