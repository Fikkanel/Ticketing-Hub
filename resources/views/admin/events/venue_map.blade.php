@extends('layouts.admin')

@section('title', 'Venue Map Builder: ' . $event->judul)

@section('content')
<style>
    #canvas-container {
        width: 100%;
        height: 600px;
        border: 1px solid #ccc;
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
    }
    canvas {
        background-color: white;
        border: 1px dashed #ddd;
    }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Venue Map Builder: {{ $event->judul }}</h1>
    <a href="{{ route('admin.events') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-3">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Toolbar</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Pilih alat dan gambar zona pada kanvas.</p>
                <div class="d-grid gap-2 mb-4">
                    <button id="add-zone-btn" class="btn btn-primary btn-sm">
                        <i class="fas fa-square"></i> Tambah Zona Baru
                    </button>
                    <button id="delete-selected-btn" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                    <button id="clear-canvas-btn" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-broom"></i> Bersihkan Kanvas
                    </button>
                </div>

                <hr>

                <div id="zone-properties" style="display: none;">
                    <h6 class="font-weight-bold">Properti Zona</h6>
                    <div class="form-group mb-2">
                        <label class="small">Nama Zona</label>
                        <input type="text" id="prop-name" class="form-control form-control-sm" placeholder="Contoh: Festival A">
                    </div>
                    <div class="form-group mb-2">
                        <label class="small">Produk (Tiket)</label>
                        <select id="prop-product" class="form-control form-control-sm">
                            <option value="">-- Pilih Tiket --</option>
                            @foreach($event->products as $product)
                                <option value="{{ $product->product_id }}" data-type="{{ $product->kategori_tiket }}">
                                    {{ $product->nama_produk }} ({{ ucfirst($product->kategori_tiket) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small">Warna</label>
                        <input type="color" id="prop-color" class="form-control form-control-sm form-control-color w-100">
                    </div>
                    <button id="save-prop-btn" class="btn btn-success btn-sm w-100">Simpan Properti</button>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-body">
                <button id="save-map-btn" class="btn btn-info w-100 btn-lg font-weight-bold">
                    <i class="fas fa-save"></i> Simpan Venue Map
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <div id="canvas-container" class="d-flex justify-content-center align-items-center">
                    <canvas id="venue-canvas" width="800" height="600"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    // Initialize Fabric Canvas
    const canvas = new fabric.Canvas('venue-canvas', {
        preserveObjectStacking: true,
        selection: true
    });

    let zoneCounter = 1;
    const existingMapData = {!! $venueMap ? json_encode($venueMap->canvas_data) : 'null' !!};
    const existingZones = {!! $zones ? json_encode($zones) : '[]' !!};

    // Load existing map if any
    if (existingZones && existingZones.length > 0) {
        existingZones.forEach(zone => {
            try {
                const coords = typeof zone.coordinates === 'string' ? JSON.parse(zone.coordinates) : zone.coordinates;
                const rect = new fabric.Rect({
                    left: 0, top: 0,
                    width: coords.width,
                    height: coords.height,
                    fill: coords.fill,
                    opacity: 0.8,
                    originX: 'center',
                    originY: 'center',
                    rx: 5, ry: 5
                });

                const text = new fabric.Text(zone.name, {
                    left: 0, top: 0,
                    fontSize: 18,
                    fontWeight: 'bold',
                    fill: '#fff',
                    originX: 'center',
                    originY: 'center',
                    shadow: new fabric.Shadow({ color: 'rgba(0,0,0,0.5)', blur: 2, offsetX: 1, offsetY: 1 })
                });

                const group = new fabric.Group([rect, text], {
                    left: coords.left,
                    top: coords.top,
                    angle: coords.angle,
                    originX: 'center',
                    originY: 'center',
                    hasControls: true,
                    hasBorders: true
                });
                
                group.set('zoneData', {
                    id: zone.id,
                    name: zone.name,
                    productId: zone.product_id,
                    type: zone.type,
                    fill: coords.fill
                });

                canvas.add(group);
                zoneCounter++;
            } catch (e) {
                console.error("Error loading zone", e);
            }
        });
    }

    // Add Zone Button
    document.getElementById('add-zone-btn').addEventListener('click', function() {
        const defaultColor = '#3498db';
        const rect = new fabric.Rect({
            left: 0, top: 0,
            width: 150,
            height: 100,
            fill: defaultColor,
            opacity: 0.8,
            originX: 'center',
            originY: 'center',
            rx: 5, ry: 5
        });

        const text = new fabric.Text('Zona Baru', {
            left: 0, top: 0,
            fontSize: 18,
            fontWeight: 'bold',
            fill: '#fff',
            originX: 'center',
            originY: 'center',
            shadow: new fabric.Shadow({ color: 'rgba(0,0,0,0.5)', blur: 2, offsetX: 1, offsetY: 1 })
        });

        const group = new fabric.Group([rect, text], {
            left: canvas.width / 2,
            top: canvas.height / 2,
            originX: 'center',
            originY: 'center',
            hasControls: true,
            hasBorders: true
        });

        group.set('zoneData', {
            id: null,
            name: 'Zona Baru',
            productId: '',
            type: '',
            fill: defaultColor
        });

        canvas.add(group);
        canvas.setActiveObject(group);
    });

    // Delete Selected
    document.getElementById('delete-selected-btn').addEventListener('click', function() {
        const activeObject = canvas.getActiveObject();
        if (activeObject) {
            Swal.fire({
                title: 'Hapus zona terpilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    canvas.remove(activeObject);
                    document.getElementById('zone-properties').style.display = 'none';
                }
            });
        } else {
            Swal.fire('Peringatan', 'Pilih zona terlebih dahulu', 'warning');
        }
    });

    // Clear Canvas
    document.getElementById('clear-canvas-btn').addEventListener('click', function() {
        Swal.fire({
            title: 'Bersihkan seluruh kanvas?',
            text: 'Semua zona yang belum disimpan akan hilang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Bersihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                canvas.clear();
                document.getElementById('zone-properties').style.display = 'none';
            }
        });
    });

    // Handle Selection
    canvas.on('selection:created', showProperties);
    canvas.on('selection:updated', showProperties);
    canvas.on('selection:cleared', hideProperties);

    function showProperties(e) {
        const obj = e.selected[0];
        if (!obj || !obj.zoneData) return;

        document.getElementById('zone-properties').style.display = 'block';
        document.getElementById('prop-name').value = obj.zoneData.name || '';
        document.getElementById('prop-product').value = obj.zoneData.productId || '';
        document.getElementById('prop-color').value = obj.zoneData.fill || '#3498db';
    }

    function hideProperties() {
        document.getElementById('zone-properties').style.display = 'none';
    }

    // Save Properties
    document.getElementById('save-prop-btn').addEventListener('click', function() {
        const obj = canvas.getActiveObject();
        if (!obj || !obj.zoneData) return;

        const name = document.getElementById('prop-name').value;
        const productId = document.getElementById('prop-product').value;
        const color = document.getElementById('prop-color').value;
        
        const selectEl = document.getElementById('prop-product');
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const type = selectedOption ? selectedOption.getAttribute('data-type') : '';

        if (!name || !productId) {
            Swal.fire('Peringatan', 'Nama Zona dan Produk harus diisi!', 'warning');
            return;
        }

        obj.zoneData.name = name;
        obj.zoneData.productId = productId;
        obj.zoneData.type = type;
        obj.zoneData.fill = color;

        // Update visuals inside group
        const rect = obj.item(0);
        const text = obj.item(1);
        
        rect.set('fill', color);
        text.set('text', name);
        
        canvas.renderAll();
        Toast.fire({
            icon: 'success',
            title: 'Properti zona diperbarui!'
        });
    });

    // Save Map
    document.getElementById('save-map-btn').addEventListener('click', function() {
        const objects = canvas.getObjects();
        const zones = [];
        
        let hasError = false;

        objects.forEach(obj => {
            if (obj.zoneData) {
                if (!obj.zoneData.productId) {
                    hasError = true;
                }
                
                // Get precise coordinates relative to canvas
                const bounds = obj.getBoundingRect();
                
                zones.push({
                    id: obj.zoneData.id,
                    name: obj.zoneData.name,
                    product_id: obj.zoneData.productId,
                    type: obj.zoneData.type,
                    coordinates: {
                        left: obj.left,
                        top: obj.top,
                        width: obj.width * obj.scaleX, // Base width * scaleX
                        height: obj.height * obj.scaleY, // Base height * scaleY
                        angle: obj.angle,
                        fill: obj.zoneData.fill,
                        boundingRect: bounds
                    }
                });
            }
        });

        if (hasError) {
            Swal.fire('Peringatan', 'Beberapa zona belum ditautkan ke produk tiket! Harap lengkapi sebelum menyimpan.', 'error');
            return;
        }

        const canvasData = JSON.stringify(canvas.toJSON(['zoneData']));

        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        fetch('{{ route('admin.events.venue_map.update', $event->event_id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                canvas_data: canvasData,
                zones: zones
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Berhasil!', 'Venue Map berhasil disimpan.', 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Gagal!', 'Gagal menyimpan: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
</script>
@endsection
