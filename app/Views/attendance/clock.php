<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .clock-container {
        text-align: center;
        padding: 3rem;
    }
    
    .time-display {
        font-size: 5rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .date-display {
        font-size: 1.5rem;
        color: #6c757d;
    }
    
    .btn-clock {
        padding: 1.5rem 3rem;
        font-size: 1.25rem;
        border-radius: 50px;
    }
    
    .camera-preview {
        width: 100%;
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        background: #000;
        display: block;
        margin: 0 auto;
        object-fit: cover;
    }
    
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        background: #000;
        border-radius: 10px;
        overflow: hidden;
    }
    
    #clockModal .modal-dialog {
        max-width: 800px;
    }
    
    #clockModal .modal-body {
        padding: 2rem;
    }
    
    .location-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
    }
    
    #locationAddress {
        padding: 0.75rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #495057;
        min-height: 60px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1>Absen Masuk/Pulang</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body clock-container">
                <div class="time-display" id="currentTime">--:--:--</div>
                <div class="date-display" id="currentDate">Loading...</div>
                
                <?php if ($todayAttendance): ?>
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-success text-white rounded p-3 mb-3 mb-md-0">
                                    <h5><i class="fas fa-sign-in-alt me-2"></i> Jam Masuk</h5>
                                    <h2><?= date('H:i:s', strtotime($todayAttendance['clock_in'])) ?></h2>
                                    <?php if ($todayAttendance['status'] === 'late'): ?>
                                    <span class="badge bg-warning text-dark">Terlambat <?= $todayAttendance['late_minutes'] ?> menit</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if ($todayAttendance['clock_out']): ?>
                                <div class="bg-danger text-white rounded p-3">
                                    <h5><i class="fas fa-sign-out-alt me-2"></i> Jam Pulang</h5>
                                    <h2><?= date('H:i:s', strtotime($todayAttendance['clock_out'])) ?></h2>
                                    <span class="badge bg-light text-dark">Durasi: <?= $todayAttendance['work_hours'] ?> jam</span>
                                </div>
                                <?php else: ?>
                                <div class="bg-light rounded p-3">
                                    <h5 class="text-muted"><i class="fas fa-sign-out-alt me-2"></i> Jam Pulang</h5>
                                    <h2 class="text-muted">--:--:--</h2>
                                    <span class="badge bg-secondary">Belum absen pulang</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mt-5">
                    <?php if ($currentUser['employee_id'] === null): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Akun Anda tidak terhubung dengan data pegawai. Untuk melakukan absensi, hubungkan akun Anda dengan data pegawai terlebih dahulu.
                    </div>
                    <?php elseif (!$todayAttendance): ?>
                    <button type="button" class="btn btn-success btn-clock" id="btnClockIn" onclick="showClockModal('in')">
                        <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
                    </button>
                    <?php elseif (!$todayAttendance['clock_out']): ?>
                    <button type="button" class="btn btn-danger btn-clock" id="btnClockOut" onclick="showClockModal('out')">
                        <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang
                    </button>
                    <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Anda sudah menyelesaikan absensi hari ini
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Schedule Info -->
        <?php if ($schedule): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i> Jadwal Kerja Anda
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h5 class="text-muted">Jam Masuk</h5>
                        <h3><?= $schedule['clock_in'] ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-muted">Jam Pulang</h5>
                        <h3><?= $schedule['clock_out'] ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-muted">Toleransi</h5>
                        <h3><?= $schedule['late_tolerance'] ?> menit</h3>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Clock Modal -->
<div class="modal fade" id="clockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clockModalTitle">Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="clockType" value="">
                
                <!-- Camera -->
                <?php if ($settings['attendance_require_photo'] ?? false): ?>
                <div class="mb-4">
                    <h6 class="text-center"><i class="fas fa-camera me-2"></i> Kamera Absensi</h6>
                    <div class="camera-container">
                        <video id="video" class="camera-preview mb-2" autoplay playsinline></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <img id="capturedPhoto" class="camera-preview mb-2" style="display: none;">
                    </div>
                    <div class="text-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Foto akan diambil otomatis saat konfirmasi</small>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Location -->
                <div class="location-info mb-4">
                    <h6><i class="fas fa-map-marker-alt me-2"></i> Lokasi Anda</h6>
                    <div id="locationStatus" class="mb-2">
                        <span class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Mengambil lokasi...</span>
                    </div>
                    <div id="locationAddress" style="display: none;">
                        <i class="fas fa-map-pin me-2 text-primary"></i>
                        <span id="addressText">-</span>
                    </div>
                    <input type="hidden" id="latitude" value="">
                    <input type="hidden" id="longitude" value="">
                    <input type="hidden" id="address" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submitClockBtn" onclick="submitClock()" disabled>
                    <i class="fas fa-check me-2"></i> Konfirmasi Absen
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let stream = null;
    let photoData = null;
    
    // Update time
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        document.getElementById('currentTime').textContent = timeStr;
        document.getElementById('currentDate').textContent = dateStr;
    }
    
    setInterval(updateClock, 1000);
    updateClock();
    
    // Show clock modal
    function showClockModal(type) {
        document.getElementById('clockType').value = type;
        document.getElementById('clockModalTitle').textContent = type === 'in' ? 'Absen Masuk' : 'Absen Pulang';
        
        const modal = new bootstrap.Modal(document.getElementById('clockModal'));
        modal.show();
        
        // Start camera
        <?php if ($settings['attendance_require_photo'] ?? false): ?>
        startCamera();
        <?php endif; ?>
        
        // Get location
        getLocation();
    }
    
    // Start camera
    function startCamera() {
        const video = document.getElementById('video');
        photoData = null; // Reset photo data
        
        navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user', width: 640, height: 480 } 
        })
        .then(function(s) {
            stream = s;
            video.srcObject = stream;
            video.style.display = 'block';
            document.getElementById('capturedPhoto').style.display = 'none';
            
            // Wait for video to be ready
            video.onloadedmetadata = function() {
                checkSubmitReady();
            };
        })
        .catch(function(err) {
            console.error('Camera error:', err);
            Swal.fire('Error', 'Tidak dapat mengakses kamera', 'error');
        });
    }
    
    // Capture photo silently (called automatically on submit)
    function capturePhotoSilent() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            console.error('Video not ready');
            return;
        }
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        // Use higher quality (0.9) for better image quality
        photoData = canvas.toDataURL('image/jpeg', 0.9);
        
        // Stop camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }
    
    // Capture photo (manual - not used but kept for compatibility)
    function capturePhoto() {
        capturePhotoSilent();
        
        const video = document.getElementById('video');
        const capturedPhoto = document.getElementById('capturedPhoto');
        capturedPhoto.src = photoData;
        video.style.display = 'none';
        capturedPhoto.style.display = 'block';
        
        checkSubmitReady();
    }
    
    // Retake photo
    function retakePhoto() {
        photoData = null;
        startCamera();
        checkSubmitReady();
    }
    
    // Get location
    function getLocation() {
        const locationStatus = document.getElementById('locationStatus');
        const locationAddress = document.getElementById('locationAddress');
        const addressText = document.getElementById('addressText');
        
        if (!navigator.geolocation) {
            locationStatus.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-2"></i>Geolocation tidak didukung</span>';
            return;
        }
        
        locationStatus.style.display = 'block';
        locationAddress.style.display = 'none';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                locationStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-2"></i>Lokasi berhasil diambil</span>';
                
                // Reverse geocode
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || 'Alamat tidak ditemukan';
                        document.getElementById('address').value = address;
                        addressText.textContent = address;
                        locationAddress.style.display = 'block';
                        locationStatus.style.display = 'none';
                    })
                    .catch(err => {
                        addressText.textContent = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        locationAddress.style.display = 'block';
                        locationStatus.style.display = 'none';
                    });
                
                checkSubmitReady();
            },
            function(error) {
                let message = 'Gagal mengambil lokasi';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Akses lokasi ditolak';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        message = 'Timeout mengambil lokasi';
                        break;
                }
                locationStatus.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-2"></i>${message}</span>`;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
    
    // Check if submit is ready
    function checkSubmitReady() {
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        
        <?php if ($settings['attendance_require_photo'] ?? false): ?>
        // Check if camera is ready (video has dimensions)
        const video = document.getElementById('video');
        const cameraReady = video && video.videoWidth > 0;
        let ready = latitude && longitude && (cameraReady || photoData);
        <?php else: ?>
        let ready = latitude && longitude;
        <?php endif; ?>
        
        document.getElementById('submitClockBtn').disabled = !ready;
    }
    
    // Submit clock
    function submitClock() {
        const type = document.getElementById('clockType').value;
        const url = type === 'in' ? '<?= base_url('attendance/clock-in') ?>' : '<?= base_url('attendance/clock-out') ?>';
        
        <?php if ($settings['attendance_require_photo'] ?? false): ?>
        // Capture photo automatically if not captured yet
        if (!photoData) {
            capturePhotoSilent();
        }
        <?php endif; ?>
        
        showLoading();
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value,
                address: document.getElementById('address').value,
                photo: photoData
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            }
        });
    }
    
    // Clean up on modal close
    document.getElementById('clockModal').addEventListener('hidden.bs.modal', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>
<?= $this->endSection() ?>
