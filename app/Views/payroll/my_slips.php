<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Slip Gaji Saya</h1>
</div>

<div class="row">
    <?php if (!empty($payslips)): ?>
    <?php foreach ($payslips as $slip): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                <h5><?= date('F Y', mktime(0, 0, 0, $slip['period_month'], 1, $slip['period_year'])) ?></h5>
                <p class="text-muted mb-2">
                    <?php
                    $statusClass = ['draft' => 'secondary', 'approved' => 'info', 'paid' => 'success'];
                    $statusText = ['draft' => 'Draft', 'approved' => 'Disetujui', 'paid' => 'Dibayar'];
                    ?>
                    <span class="badge bg-<?= $statusClass[$slip['status']] ?>">
                        <?= $statusText[$slip['status']] ?>
                    </span>
                </p>
                <h4 class="text-success mb-3">Rp <?= number_format($slip['net_salary'], 0, ',', '.') ?></h4>
                <?php if ($slip['status'] !== 'draft'): ?>
                <a href="<?= base_url('payroll/slip/' . $slip['id']) ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye me-1"></i> Lihat Slip
                </a>
                <?php else: ?>
                <button class="btn btn-secondary btn-sm" disabled>Belum Tersedia</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h4>Belum ada slip gaji</h4>
                <p class="text-muted">Slip gaji Anda akan muncul di sini setelah diproses oleh HR.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
