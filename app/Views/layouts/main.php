<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Sistem Absensi' ?> - ABSESI</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-header {
            padding: 0.5rem 1.5rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .sidebar-menu a i {
            width: 24px;
            margin-right: 10px;
        }
        
        .sidebar-menu .submenu {
            padding-left: 2.5rem;
        }
        
        .sidebar-menu .submenu a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .sidebar-collapsed .main-content {
            margin-left: 0;
        }
        
        /* Navbar */
        .navbar-main {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0.75rem 1.5rem;
        }
        
        .navbar-main .btn-toggle-sidebar {
            border: none;
            background: transparent;
            font-size: 1.25rem;
            color: var(--dark-color);
        }
        
        .user-dropdown img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Content */
        .content-wrapper {
            padding: 1.5rem;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        
        /* Stats Cards */
        .stat-card {
            border-radius: 10px;
            padding: 1.5rem;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-card.primary { background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); }
        .stat-card.success { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .stat-card.danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
        .stat-card.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card .stat-content p {
            margin: 0;
            opacity: 0.9;
        }
        
        /* Table */
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: var(--dark-color);
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        /* Badge */
        .badge-status {
            padding: 0.4em 0.8em;
            border-radius: 20px;
            font-weight: 500;
        }
        
        /* Form */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            padding: 8px 0;
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
        }
        
        .mobile-bottom-nav .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .mobile-bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            padding: 4px 12px;
            border-radius: 12px;
            transition: all 0.2s ease;
            min-width: 60px;
        }
        
        .mobile-bottom-nav .nav-item:hover,
        .mobile-bottom-nav .nav-item.active {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
        }
        
        .mobile-bottom-nav .nav-item i {
            font-size: 1.3rem;
            margin-bottom: 2px;
        }
        
        .mobile-bottom-nav .nav-item span {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        
        .mobile-bottom-nav .nav-item.menu-toggle {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            padding: 10px 16px;
            border-radius: 50%;
            margin-top: -20px;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }
        
        .mobile-bottom-nav .nav-item.menu-toggle:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: #fff;
        }
        
        .mobile-bottom-nav .nav-item.menu-toggle i {
            margin-bottom: 0;
        }
        
        .mobile-bottom-nav .nav-item.menu-toggle span {
            display: none;
        }
        
        /* Mobile responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar-mobile-show .sidebar {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding-bottom: 80px; /* Space for bottom nav */
            }
            
            .mobile-bottom-nav {
                display: block;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-mobile-show .sidebar-overlay {
                display: block;
            }
            
            /* Hide desktop toggle on mobile when bottom nav is visible */
            .btn-toggle-sidebar {
                display: none;
            }
        }
        
        @media (max-width: 575.98px) {
            .mobile-bottom-nav .nav-item {
                padding: 4px 8px;
                min-width: 50px;
            }
            
            .mobile-bottom-nav .nav-item span {
                font-size: 0.6rem;
            }
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-fingerprint fa-2x me-2"></i>
            <h4>ABSESI</h4>
        </div>
        
        <nav class="sidebar-menu">
            <a href="<?= base_url('dashboard') ?>" class="<?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="menu-header">Menu Utama</div>
            
            <a href="<?= base_url('attendance/clock') ?>" class="<?= strpos(uri_string(), 'attendance/clock') !== false ? 'active' : '' ?>">
                <i class="fas fa-clock"></i>
                <span>Absen Masuk/Pulang</span>
            </a>
            
            <a href="<?= base_url('attendance/history') ?>" class="<?= strpos(uri_string(), 'attendance/history') !== false ? 'active' : '' ?>">
                <i class="fas fa-history"></i>
                <span>Riwayat Absensi</span>
            </a>

            <a href="<?= base_url('attendance/overtime') ?>" class="<?= strpos(uri_string(), 'attendance/overtime') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-clock"></i>
                <span>Pengajuan Lembur</span>
            </a>
            
            <a href="<?= base_url('leave') ?>" class="<?= uri_string() == 'leave' || strpos(uri_string(), 'leave/') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-minus"></i>
                <span>Pengajuan Cuti</span>
            </a>
            
            <a href="<?= base_url('payroll/my-slips') ?>" class="<?= strpos(uri_string(), 'payroll/my-slips') !== false ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Slip Gaji Saya</span>
            </a>
            
            <?php if (isset($currentUser) && in_array($currentUser['group_id'], [1, 2, 3, 4])): ?>
            <div class="menu-header">Manajemen</div>
            
            <a href="<?= base_url('employees') ?>" class="<?= uri_string() == 'employees' || strpos(uri_string(), 'employees/') !== false ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Data Pegawai</span>
            </a>
            
            <a href="<?= base_url('attendance/recap') ?>" class="<?= strpos(uri_string(), 'attendance/recap') !== false ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Rekap Absensi</span>
            </a>
            
            <a href="<?= base_url('leave/approval') ?>" class="<?= strpos(uri_string(), 'leave/approval') !== false ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i>
                <span>Persetujuan Cuti</span>
            </a>
            
            <a href="<?= base_url('payroll') ?>" class="<?= uri_string() == 'payroll' || strpos(uri_string(), 'payroll/') !== false && strpos(uri_string(), 'my-slips') === false ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Penggajian</span>
            </a>
            <?php endif; ?>
            
            <?php if (isset($currentUser) && in_array($currentUser['group_id'], [1, 2])): ?>
            <div class="menu-header">Master Data</div>
            
            <a href="<?= base_url('master/departments') ?>" class="<?= strpos(uri_string(), 'master/departments') !== false ? 'active' : '' ?>">
                <i class="fas fa-building"></i>
                <span>Departemen</span>
            </a>
            
            <a href="<?= base_url('master/positions') ?>" class="<?= strpos(uri_string(), 'master/positions') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i>
                <span>Jabatan</span>
            </a>
            
            <a href="<?= base_url('master/schedules') ?>" class="<?= strpos(uri_string(), 'master/schedules') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Jadwal Kerja</span>
            </a>
            
            <a href="<?= base_url('master/locations') ?>" class="<?= strpos(uri_string(), 'master/locations') !== false ? 'active' : '' ?>">
                <i class="fas fa-map-marker-alt"></i>
                <span>Lokasi Kantor</span>
            </a>
            
            <a href="<?= base_url('master/holidays') ?>" class="<?= strpos(uri_string(), 'master/holidays') !== false ? 'active' : '' ?>">
                <i class="fas fa-umbrella-beach"></i>
                <span>Hari Libur</span>
            </a>
            
            <a href="<?= base_url('master/leave-types') ?>" class="<?= strpos(uri_string(), 'master/leave-types') !== false ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                <span>Jenis Cuti</span>
            </a>
            <?php endif; ?>
            
            <?php if (isset($currentUser) && $currentUser['group_id'] == 1): ?>
            <div class="menu-header">Pengaturan</div>
            
            <a href="<?= base_url('settings') ?>" class="<?= uri_string() == 'settings' ? 'active' : '' ?>">
                <i class="fas fa-cogs"></i>
                <span>Pengaturan Umum</span>
            </a>
            
            <a href="<?= base_url('settings/users') ?>" class="<?= strpos(uri_string(), 'settings/users') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i>
                <span>Manajemen User</span>
            </a>
            
            <a href="<?= base_url('settings/groups') ?>" class="<?= strpos(uri_string(), 'settings/groups') !== false ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i>
                <span>Manajemen Group</span>
            </a>
            
            <a href="<?= base_url('settings/permissions') ?>" class="<?= strpos(uri_string(), 'settings/permissions') !== false ? 'active' : '' ?>">
                <i class="fas fa-key"></i>
                <span>Hak Akses</span>
            </a>

            <a href="<?= base_url('leave-config') ?>" class="<?= strpos(uri_string(), 'leave-config') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Config Cuti Karyawan</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Navbar -->
        <nav class="navbar-main d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="text-muted d-none d-md-inline"><?= date('l, d F Y') ?></span>
            </div>
            
            <div class="dropdown user-dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= base_url('assets/img/default-avatar.png') ?>" alt="User" class="me-2">
                    <span class="d-none d-md-inline"><?= $currentUser['username'] ?? 'User' ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="content-wrapper">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?= $this->renderSection('content') ?>
        </div>
    </main>
    
    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <a href="<?= base_url('dashboard') ?>" class="nav-item <?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="<?= base_url('attendance/clock') ?>" class="nav-item <?= strpos(uri_string(), 'attendance/clock') !== false ? 'active' : '' ?>">
                <i class="fas fa-fingerprint"></i>
                <span>Absen</span>
            </a>
            <a href="javascript:void(0)" class="nav-item menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-th"></i>
            </a>
            <a href="<?= base_url('leave') ?>" class="nav-item <?= uri_string() == 'leave' || strpos(uri_string(), 'leave/') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Cuti</span>
            </a>
            <a href="<?= base_url('payroll/my-slips') ?>" class="nav-item <?= strpos(uri_string(), 'payroll/my-slips') !== false ? 'active' : '' ?>">
                <i class="fas fa-wallet"></i>
                <span>Gaji</span>
            </a>
        </div>
    </nav>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // DataTables Defaults
        $.extend( true, $.fn.dataTable.defaults, {
            language: {
                url: "<?= getenv('API_DATATABLES_LANG_URL') ?: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' ?>"
            }
        });

        // Toggle sidebar
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-mobile-show');
        }
        
        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Scroll sidebar to active menu
            const activeMenu = document.querySelector('.sidebar-menu a.active');
            if (activeMenu) {
                activeMenu.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        
        // Format currency
        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }
        
        // Show loading
        function showLoading() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Hide loading
        function hideLoading() {
            Swal.close();
        }
        
        // Show toast notification
        function showToast(type, message) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
        
        // Confirm delete
        function confirmDelete(url, message = 'Data yang dihapus tidak dapat dikembalikan!') {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                location.reload();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function() {
                            showToast('error', 'Terjadi kesalahan');
                        }
                    });
                }
            });
        }
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
