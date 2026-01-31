<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Lokasi Kantor</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i> Tambah Lokasi
    </button>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i> Daftar Lokasi
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($locations)): ?>
                    <?php foreach ($locations as $loc): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($loc['name']) ?></strong>
                            <br><small class="text-muted"><?= esc($loc['address'] ?? 'Alamat tidak tersedia') ?></small>
                            <br><small class="text-muted">Radius: <?= $loc['radius'] ?>m</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-warning" onclick='editLocation(<?= json_encode($loc) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= base_url('master/locations/' . $loc['id']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="list-group-item text-center text-muted">
                        Belum ada lokasi
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('master/locations/store') ?>" method="post" id="locationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="locationId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" name="latitude" id="latitude" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" name="longitude" id="longitude" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Radius (meter)</label>
                            <input type="number" name="radius" id="radius" class="form-control" value="100">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi di Peta</label>
                        <div class="input-group mb-2">
                            <input type="text" id="mapSearchInput" class="form-control" placeholder="Cari nama kota atau lokasi...">
                            <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                        <div id="modalMap" style="height: 300px; border-radius: 5px;"></div>
                        <small class="text-muted">Klik pada peta untuk memilih lokasi</small>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

</script>
<script>
    const OSM_TILE_URL = '<?= getenv('API_OSM_TILE_URL') ?: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' ?>';
    const NOMINATIM_SEARCH_URL = '<?= getenv('API_NOMINATIM_SEARCH_URL') ?: 'https://nominatim.openstreetmap.org/search' ?>';
    const NOMINATIM_REVERSE_URL = '<?= getenv('API_NOMINATIM_REVERSE_URL') ?: 'https://nominatim.openstreetmap.org/reverse' ?>';
</script>

<script>
    // Main map
    const map = L.map('map').setView([-6.2088, 106.8456], 12);
    L.tileLayer(OSM_TILE_URL, { maxZoom: 19 }).addTo(map);
    
    // Add markers for existing locations
    <?php foreach ($locations as $loc): ?>
    L.marker([<?= $loc['latitude'] ?>, <?= $loc['longitude'] ?>])
        .addTo(map)
        .bindPopup('<strong><?= esc($loc['name']) ?></strong><br><?= esc($loc['address'] ?? '') ?>');
    L.circle([<?= $loc['latitude'] ?>, <?= $loc['longitude'] ?>], { radius: <?= $loc['radius'] ?>, color: '#4361ee', fillOpacity: 0.2 }).addTo(map);
    <?php endforeach; ?>
    
    // Modal map
    let modalMap = null;
    let modalMarker = null;
    let modalCircle = null;
    
    function searchLocation() {
        const query = document.getElementById('mapSearchInput').value;
        if (!query) return;
        
        // Show loading state implies simple UI locking or toast
        const btn = document.querySelector('button[onclick="searchLocation()"]');
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        fetch(`${NOMINATIM_SEARCH_URL}?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    
                    modalMap.setView([lat, lon], 13);
                    // Optional: auto-place marker?
                    // Let's NOT auto-place, just center the view so user can refine click.
                } else {
                    alert('Lokasi tidak ditemukan');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal mencari lokasi');
            })
            .finally(() => {
                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;
            });
    }

    document.getElementById('mapSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent form submission
            searchLocation();
        }
    });

    document.getElementById('locationModal').addEventListener('shown.bs.modal', function() {
        if (!modalMap) {
            modalMap = L.map('modalMap').setView([-6.2088, 106.8456], 12);
            L.tileLayer(OSM_TILE_URL, { maxZoom: 19 }).addTo(modalMap);
            
            modalMap.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                
                updateModalMarker(lat, lng);
                
                // Reverse geocode
                fetch(`${NOMINATIM_REVERSE_URL}?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('address').value = data.display_name || '';
                    });
            });
        }
        
        setTimeout(() => modalMap.invalidateSize(), 100);
    });
    
    function updateModalMarker(lat, lng) {
        const radius = parseInt(document.getElementById('radius').value) || 100;
        
        if (modalMarker) {
            modalMarker.setLatLng([lat, lng]);
            modalCircle.setLatLng([lat, lng]);
            modalCircle.setRadius(radius);
        } else {
            modalMarker = L.marker([lat, lng]).addTo(modalMap);
            modalCircle = L.circle([lat, lng], { radius: radius, color: '#4361ee', fillOpacity: 0.2 }).addTo(modalMap);
        }
        
        modalMap.setView([lat, lng], 16);
    }
    
    document.getElementById('radius').addEventListener('change', function() {
        if (modalCircle) {
            modalCircle.setRadius(parseInt(this.value) || 100);
        }
    });
    
    function resetForm() {
        document.getElementById('locationForm').reset();
        document.getElementById('locationForm').action = '<?= base_url('master/locations/store') ?>';
        document.getElementById('locationId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Lokasi';
        document.getElementById('is_active').checked = true;
        
        if (modalMarker) {
            modalMap.removeLayer(modalMarker);
            modalMap.removeLayer(modalCircle);
            modalMarker = null;
            modalCircle = null;
        }
    }
    
    function editLocation(loc) {
        document.getElementById('locationForm').action = '<?= base_url('master/locations/update') ?>/' + loc.id;
        document.getElementById('locationId').value = loc.id;
        document.getElementById('name').value = loc.name;
        document.getElementById('latitude').value = loc.latitude;
        document.getElementById('longitude').value = loc.longitude;
        document.getElementById('radius').value = loc.radius;
        document.getElementById('address').value = loc.address || '';
        document.getElementById('is_active').checked = loc.is_active == 1;
        document.getElementById('modalTitle').textContent = 'Edit Lokasi';
        
        new bootstrap.Modal(document.getElementById('locationModal')).show();
        
        setTimeout(() => {
            if (modalMap) {
                updateModalMarker(parseFloat(loc.latitude), parseFloat(loc.longitude));
            }
        }, 300);
    }
</script>
<?= $this->endSection() ?>
