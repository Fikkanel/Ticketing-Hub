@extends('layouts.admin')

@section('title', 'Atur Layout Kursi: ' . $product->nama_produk)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white pb-0">
                <h6 class="fw-bold">Konfigurasi Grid</h6>
            </div>
            <div class="card-body">
                <form id="grid-config-form">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Baris (Vertikal)</label>
                        <input type="number" id="input-rows" class="form-control" value="{{ $rows }}" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Kolom (Horizontal)</label>
                        <input type="number" id="input-columns" class="form-control" value="{{ $columns }}" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Posisi Panggung</label>
                        <select id="input-stage" class="form-select">
                            <option value="top" {{ $stagePosition == 'top' ? 'selected' : '' }}>Atas (Top)</option>
                            <option value="bottom" {{ $stagePosition == 'bottom' ? 'selected' : '' }}>Bawah (Bottom)</option>
                            <option value="left" {{ $stagePosition == 'left' ? 'selected' : '' }}>Kiri (Left)</option>
                            <option value="right" {{ $stagePosition == 'right' ? 'selected' : '' }}>Kanan (Right)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bentuk Panggung</label>
                        <select id="input-shape" class="form-select">
                            <option value="normal" {{ $stageShape == 'normal' ? 'selected' : '' }}>Normal (Persegi)</option>
                            <option value="convex" {{ $stageShape == 'convex' ? 'selected' : '' }}>Melengkung Keluar (Convex)</option>
                            <option value="concave" {{ $stageShape == 'concave' ? 'selected' : '' }}>Melengkung ke Dalam (Concave)</option>
                        </select>
                    </div>
                </form>

                <hr>

                <div class="small text-muted mb-3">
                    <p class="mb-1"><i class="fas fa-info-circle text-primary"></i> <b>Petunjuk:</b></p>
                    <ul class="ps-3 mb-0">
                        <li>Klik kursi di grid untuk menonaktifkan (abu-abu) dan menjadikannya jalan/ruang kosong.</li>
                        <li>Klik lagi untuk mengaktifkannya kembali.</li>
                        <li>Nomor kursi (mis. A1, B2) akan otomatis disesuaikan dengan posisi panggung.</li>
                    </ul>
                </div>

                <form action="{{ route('admin.products.seat_layout.store', $product->product_id) }}" method="POST" id="save-layout-form">
                    @csrf
                    <input type="hidden" name="rows" id="form-rows" value="{{ $rows }}">
                    <input type="hidden" name="columns" id="form-columns" value="{{ $columns }}">
                    <input type="hidden" name="stage_position" id="form-stage" value="{{ $stagePosition }}">
                    <input type="hidden" name="stage_shape" id="form-shape" value="{{ $stageShape }}">
                    <div id="seats-input-container"></div>
                    <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fas fa-save me-1"></i> Simpan Layout</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Visualisasi Layout Kursi</h6>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-zoom-out" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-zoom-reset" title="Reset Zoom"><i class="fas fa-compress"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-zoom-in" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                    <span class="badge bg-primary ms-2" id="active-seats-count">0 Kursi Aktif</span>
                </div>
            </div>
            <div class="card-body bg-light position-relative" style="min-height: 500px; overflow: hidden;" id="panzoom-parent">
                <div id="seat-layout-container" class="position-relative p-4 bg-white shadow-sm border rounded" style="width: max-content; margin: auto; transform-origin: center center;">
                    {{-- Layout generated by JS --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .seat-grid {
        display: grid;
        gap: 8px;
        margin: 20px;
    }
    .seat-cell {
        width: 40px;
        height: 40px;
        border-radius: 8px 8px 4px 4px; /* Default top curve representing seat */
        background-color: #0d6efd; /* Primary color */
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .seat-cell:hover {
        transform: scale(1.1);
    }
    .seat-cell.inactive {
        background-color: #e9ecef;
        color: #adb5bd;
        box-shadow: none;
        border-radius: 4px; /* Flat for inactive */
    }
    .stage {
        background-color: #343a40;
        color: white;
        text-align: center;
        font-weight: bold;
        padding: 10px;
        border-radius: 4px;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    .stage.top { top: 0; left: 20px; right: 20px; height: 30px; }
    .stage.bottom { bottom: 0; left: 20px; right: 20px; height: 30px; }
    .stage.left { left: 0; top: 20px; bottom: 20px; width: 30px; writing-mode: vertical-rl; text-orientation: mixed; }
    .stage.right { right: 0; top: 20px; bottom: 20px; width: 30px; writing-mode: vertical-rl; text-orientation: mixed; }
    
    /* Stage Shapes */
    .stage.top.convex { border-radius: 0 0 50% 50% / 0 0 20px 20px; }
    .stage.top.concave { border-radius: 50% 50% 0 0 / 20px 20px 0 0; }
    
    .stage.bottom.convex { border-radius: 50% 50% 0 0 / 20px 20px 0 0; }
    .stage.bottom.concave { border-radius: 0 0 50% 50% / 0 0 20px 20px; }
    
    .stage.left.convex { border-radius: 0 50% 50% 0 / 0 20px 20px 0; }
    .stage.left.concave { border-radius: 50% 0 0 50% / 20px 0 0 20px; }
    
    .stage.right.convex { border-radius: 50% 0 0 50% / 20px 0 0 20px; }
    .stage.right.concave { border-radius: 0 50% 50% 0 / 0 20px 20px 0; }
    
    /* Seat orientation based on stage */
    .facing-top { border-radius: 4px 4px 12px 12px; border-top: 4px solid #0a58ca; }
    .facing-bottom { border-radius: 12px 12px 4px 4px; border-bottom: 4px solid #0a58ca; }
    .facing-left { border-radius: 4px 12px 12px 4px; border-left: 4px solid #0a58ca; }
    .facing-right { border-radius: 12px 4px 4px 12px; border-right: 4px solid #0a58ca; }
    
    .seat-cell.inactive { border: none !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let seatsData = {!! json_encode($seatsData) !!};
    let activeSeatsCount = 0;

    function getLetter(index) {
        let letter = '';
        while (index >= 0) {
            letter = String.fromCharCode((index % 26) + 65) + letter;
            index = Math.floor(index / 26) - 1;
        }
        return letter;
    }

    function generateLayout() {
        const rows = parseInt(document.getElementById('input-rows').value) || 10;
        const columns = parseInt(document.getElementById('input-columns').value) || 10;
        const stagePos = document.getElementById('input-stage').value;
        const stageShape = document.getElementById('input-shape').value;

        // Update hidden form inputs
        document.getElementById('form-rows').value = rows;
        document.getElementById('form-columns').value = columns;
        document.getElementById('form-stage').value = stagePos;
        document.getElementById('form-shape').value = stageShape;

        const container = document.getElementById('seat-layout-container');
        container.innerHTML = '';

        // Add stage
        const stage = document.createElement('div');
        stage.className = `stage ${stagePos} ${stageShape}`;
        stage.innerText = 'PANGGUNG';
        container.appendChild(stage);

        // Add grid
        const grid = document.createElement('div');
        grid.className = 'seat-grid';
        grid.style.gridTemplateColumns = `repeat(${columns}, 40px)`;
        grid.style.gridTemplateRows = `repeat(${rows}, 40px)`;
        
        // Add padding to grid based on stage position
        grid.style.marginTop = stagePos === 'top' ? '40px' : '20px';
        grid.style.marginBottom = stagePos === 'bottom' ? '40px' : '20px';
        grid.style.marginLeft = stagePos === 'left' ? '40px' : '20px';
        grid.style.marginRight = stagePos === 'right' ? '40px' : '20px';

        let facingClass = '';
        if(stagePos === 'top') facingClass = 'facing-top';
        if(stagePos === 'bottom') facingClass = 'facing-bottom';
        if(stagePos === 'left') facingClass = 'facing-left';
        if(stagePos === 'right') facingClass = 'facing-right';

        activeSeatsCount = 0;
        document.getElementById('seats-input-container').innerHTML = ''; // clear hidden inputs

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < columns; c++) {
                const seat = document.createElement('div');
                
                // Determine row/col index based on stage position to make labeling natural (A1 starts near stage)
                let visualRow = r;
                let visualCol = c;
                
                if (stagePos === 'bottom') { visualRow = rows - 1 - r; }
                if (stagePos === 'right') { visualCol = columns - 1 - c; }
                
                let label = '';
                if (stagePos === 'top' || stagePos === 'bottom') {
                    label = getLetter(visualRow) + (visualCol + 1);
                } else {
                    label = getLetter(visualCol) + (visualRow + 1);
                }

                seat.innerText = label;
                seat.dataset.row = r;
                seat.dataset.col = c;
                seat.dataset.label = label;
                
                // Check if existing data
                let isActive = true;
                if (seatsData && seatsData[r] && seatsData[r][c]) {
                    isActive = seatsData[r][c].is_active;
                }

                seat.dataset.active = isActive ? 'true' : 'false';
                seat.className = `seat-cell ${facingClass}`;
                if (!isActive) {
                    seat.classList.add('inactive');
                    seat.innerText = '';
                } else {
                    activeSeatsCount++;
                }

                seat.addEventListener('click', function() {
                    let currentlyActive = this.dataset.active === 'true';
                    let newActive = !currentlyActive;
                    
                    this.dataset.active = newActive ? 'true' : 'false';
                    if (newActive) {
                        this.classList.remove('inactive');
                        this.innerText = this.dataset.label;
                        activeSeatsCount++;
                    } else {
                        this.classList.add('inactive');
                        this.innerText = '';
                        activeSeatsCount--;
                    }
                    updateSeatsCount();
                    updateHiddenInput(this.dataset.row, this.dataset.col, newActive, this.dataset.label);
                });

                grid.appendChild(seat);
                
                // Initialize hidden inputs
                createHiddenInput(r, c, isActive, label);
            }
        }
        
        container.appendChild(grid);
        updateSeatsCount();
    }

    function createHiddenInput(r, c, active, label) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `seats[]`;
        input.id = `input-seat-${r}-${c}`;
        input.value = JSON.stringify({row: r, col: c, active: active, label: label});
        document.getElementById('seats-input-container').appendChild(input);
    }

    function updateHiddenInput(r, c, active, label) {
        const input = document.getElementById(`input-seat-${r}-${c}`);
        if(input) {
            input.value = JSON.stringify({row: r, col: c, active: active, label: label});
        }
    }

    function updateSeatsCount() {
        document.getElementById('active-seats-count').innerText = `${activeSeatsCount} Kursi Aktif`;
    }

    function saveCurrentSeatsToData() {
        const currentSeats = {};
        document.querySelectorAll('.seat-cell').forEach(cell => {
            if(cell.dataset.row !== undefined) {
                const r = cell.dataset.row;
                const c = cell.dataset.col;
                if(!currentSeats[r]) currentSeats[r] = {};
                currentSeats[r][c] = { is_active: cell.dataset.active === 'true' };
            }
        });
        seatsData = currentSeats;
    }

    document.getElementById('grid-config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCurrentSeatsToData();
        generateLayout();
    });

    const updateGridOnInput = function() {
        saveCurrentSeatsToData();
        generateLayout();
    };

    document.getElementById('input-rows').addEventListener('change', updateGridOnInput);
    document.getElementById('input-columns').addEventListener('change', updateGridOnInput);

    // Realtime UI updates for dropdowns
    document.getElementById('input-stage').addEventListener('change', function() {
        saveCurrentSeatsToData();
        generateLayout();
    });
    
    document.getElementById('input-shape').addEventListener('change', function() {
        saveCurrentSeatsToData();
        generateLayout();
    });

    // Initial render
    generateLayout();

    // Init Panzoom
    const container = document.getElementById('seat-layout-container');
    const panzoom = Panzoom(container, {
        maxScale: 3,
        minScale: 0.1,
        step: 0.2,
        cursor: 'grab'
    });

    const parent = document.getElementById('panzoom-parent');
    parent.addEventListener('wheel', panzoom.zoomWithWheel);

    // Zoom buttons
    document.getElementById('btn-zoom-in').addEventListener('click', panzoom.zoomIn);
    document.getElementById('btn-zoom-out').addEventListener('click', panzoom.zoomOut);
    document.getElementById('btn-zoom-reset').addEventListener('click', panzoom.reset);

});
</script>
@endsection
