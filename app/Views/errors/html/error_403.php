<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 150px;
            font-weight: bold;
            text-shadow: 4px 4px 0 rgba(0,0,0,0.1);
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <div class="error-message">Akses Ditolak</div>
        <p class="mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-light btn-lg">
            <i class="fas fa-home me-2"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
