@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <style>
        .trans-icon { transition: transform 0.3s ease; }
        [aria-expanded="true"] .trans-icon { transform: rotate(180deg); }
    </style>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">Dashboard Overview</h6>
            <a href="#" class="btn btn-sm btn-primary shadow-sm disabled">
                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
            </a>
        </div>
    </div>

    {{-- Event Switcher for Multi-Event Admins --}}
    @if(isset($myEvents) && $myEvents->count() > 1)
        <div class="mb-4">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="d-inline-block">
                <div class="input-group">
                    <label class="input-group-text bg-primary text-white" for="eventSelector"><i class="fas fa-filter"></i></label>
                    <select class="form-select" id="eventSelector" name="event_id" onchange="this.form.submit()">
                        <option value="">Lihat Semua Event Saya (Agregat)</option>
                        @foreach($myEvents as $evt)
                            <option value="{{ $evt->event_id }}" {{ (isset($selectedEventId) && $selectedEventId == $evt->event_id) ? 'selected' : '' }}>
                                {{ $evt->judul }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif

    {{-- BARIS KARTU STATISTIK --}}
    {{-- Mobile Toggle Button --}}
    <button class="btn btn-outline-primary d-md-none w-100 mb-3 d-flex justify-content-between align-items-center shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#statsCollapse" aria-expanded="false" aria-controls="statsCollapse">
        <span class="fw-bold"><i class="fas fa-chart-pie me-2"></i> Ringkasan Statistik</span>
        <i class="fas fa-chevron-down trans-icon"></i>
    </button>
    
    {{-- Wrapper Collapse (Mobile: Hidden default, Desktop: Always Block) --}}
    <div class="collapse d-md-block" id="statsCollapse">
        <div class="row">

        {{-- Kartu Total Order (Earnings) --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2" style="border-left: 5px solid var(--admin-primary);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Pesanan</div>
                            {{-- DATA DINAMIS --}}
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalOrders }} Pesanan</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Pendapatan (Revenue) --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-success shadow h-100 py-2" style="border-left: 5px solid #1cc88a;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Pendapatan</div>
                            {{-- DATA DINAMIS (Format Rupiah) --}}
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Event Aktif --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-info shadow h-100 py-2" style="border-left: 5px solid #36b9cc;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Event Aktif</div>
                            {{-- DATA DINAMIS --}}
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $activeEvents }} Event</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Produk Terjual --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-warning shadow h-100 py-2" style="border-left: 5px solid #f6c23e;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Produk Terlaris</div>
                            {{-- DATA DINAMIS --}}
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $bestSellingProduct }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Total Pemasukan Admin Fee --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 5px solid #6f42c1;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: #6f42c1;">Total Pemasukan (Admin Fee)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                Rp {{ number_format($totalAdminFee, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Total Pemasukan Platform Fee (Payment Gateway) --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 5px solid #e74a3b;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1" style="color: #e74a3b;">Total Pemasukan (Platform Fee)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div> {{-- End Collapse Wrapper --}}

    {{-- KONTEN UTAMA: Ilustrasi Sederhana --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        Selamat Datang, {{ Auth::user()->name }}!
                        @if(isset($currentEvent) && $currentEvent)
                            <span class="badge bg-info ms-2"><i class="fas fa-calendar-alt me-1"></i> Filter: {{ $currentEvent->judul }}</span>
                        @elseif(isset($selectedEventId) && $selectedEventId == null && !Auth::user()->isSuperAdmin())
                            <span class="badge bg-secondary ms-2"><i class="fas fa-layer-group me-1"></i> Semua Event Anda</span>
                        @elseif(Auth::user()->isSuperAdmin())
                            <span class="badge bg-danger ms-2">Superadmin Access</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    {{-- ROW CHART: SALES TREND --}}
                    <div class="row mb-4">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 fw-bold text-primary">Tren Pendapatan (30 Hari Terakhir)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area" style="position: relative; height: 300px;">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ROW CHART: TOP PRODUCTS & CHECK-IN --}}
                        <div class="col-xl-4 col-lg-5">
                            
                            {{-- Check-in Widget --}}
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-info">Real-time Check-in</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="h5 mb-2 fw-bold text-gray-800">
                                        {{ $checkedInCount }} / {{ $totalTickets }} Tiket
                                    </div>
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $checkInPercentage }}%" aria-valuenow="{{ $checkInPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $checkInPercentage }}%
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-0">Tamu telah check-in</p>
                                </div>
                            </div>

                            {{-- Top Products Widget --}}
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-warning">Top 5 Produk Terlaris</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2" style="position: relative; height: 230px;">
                                        <canvas id="topProductsChart"></canvas>
                                    </div>
                                    @if(count($topProductLabels) == 0)
                                        <p class="text-center text-muted small mt-2">Belum ada data penjualan.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Set Font Family default sama dengan CSS
    Chart.defaults.font.family = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#858796';

    // --- 1. REVENUE CHART (LINE) ---
    const ctxRevenue = document.getElementById("revenueChart");
    if (ctxRevenue) {
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartDates) !!}, // ['01 Dec', '02 Dec', ...]
                datasets: [{
                    label: "Pendapatan",
                    lineTension: 0.3,
                    backgroundColor: "rgba(58, 125, 68, 0.08)",
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                    pointRadius: 3,
                    pointBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                    pointBorderColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                    pointHoverBorderColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: {!! json_encode($chartRevenue) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { maxTicksLimit: 7 }
                    },
                    y: {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID'); // Format Rupiah Axis
                            }
                        },
                        grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleMarginBottom: 10,
                        titleColor: '#6e707e',
                        titleFont: { size: 14 },
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem) {
                                return 'Pendapatan: Rp ' + tooltipItem.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    // --- 2. TOP PRODUCTS CHART (BAR / PIE) ---
    const ctxTop = document.getElementById("topProductsChart");
    if (ctxTop) {
        new Chart(ctxTop, {
            type: 'bar', // Bisa ganti 'doughnut' atau 'pie'
            data: {
                labels: {!! json_encode($topProductLabels) !!},
                datasets: [{
                    label: "Terjual",
                    backgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#3a7d44',
                        '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                    ],
                    hoverBackgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--admin-secondary').trim() || '#2e6636',
                        '#17a673', '#2c9faf', '#dda20a', '#be2617'
                    ],
                    borderColor: 'transparent',
                    data: {!! json_encode($topProductQty) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                indexAxis: 'y', // Horizontal Bar
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' items terjual';
                            }
                        }
                    }
                },
                scales: {
                    x: { ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endsection
