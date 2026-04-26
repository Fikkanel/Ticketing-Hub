@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <h6 class="m-0 fw-bold text-primary align-self-start align-self-md-center">Daftar Pesanan Masuk</h6>
            
            <div class="d-flex flex-column flex-md-row w-100 w-md-auto gap-2">
                <form method="GET" action="{{ route('admin.orders') }}" class="d-flex align-items-center w-100 w-md-auto">
                    <select name="event_id" id="event_id" class="form-select form-select-sm w-100" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">-- Semua Event --</option>
                        @foreach($events as $id => $judul)
                            <option value="{{ $id }}" {{ $selectedEventId == $id ? 'selected' : '' }}>
                                {{ Str::limit($judul, 25) }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="input-group w-100 w-md-auto" style="min-width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input type="text" id="searchOrder" class="form-control border-start-0 bg-light small" placeholder="Cari ID/Nama..." value="{{ request('search') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-uppercase small fw-bold text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 15%">Invoice ID</th>
                            <th style="width: 25%">Pelanggan</th>
                            <th style="width: 15%">Total</th>
                            <th class="text-center" style="width: 10%">Status</th>
                            <th style="width: 15%">Tanggal</th>
                            <th class="text-center" style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    
                    <tbody id="ordersTableBody">
                        @include('admin.partials.order_rows', ['orders' => $orders])
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4" id="paginationContainer">
                {{ $orders->appends(['event_id' => $selectedEventId, 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchOrder');
        const tableBody = document.getElementById('ordersTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        let timeout = null;

        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const query = searchInput.value;
                const eventId = document.getElementById('event_id').value;
                const url = `{{ route('admin.orders') }}?search=${query}&event_id=${eventId}`;

                if(tableBody) tableBody.style.opacity = '0.5';

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    if(tableBody) {
                        tableBody.innerHTML = html;
                        tableBody.style.opacity = '1';
                    }
                    if(paginationContainer) paginationContainer.style.display = query.length > 0 ? 'none' : 'flex';
                });
            }, 500);
        });
    });

    // SweetAlert2 confirmation for resend ticket
    function confirmResendTicket(orderId, email) {
        Swal.fire({
            title: '<i class="fas fa-paper-plane text-primary"></i> Resend Tiket?',
            html: `<p class="mb-0">Email tiket akan dikirim ulang ke:</p><p class="fw-bold text-primary mt-2">${email}</p>`,
            icon: null,
            showCancelButton: true,
            confirmButtonColor: '{{ $globalSettings["primary_color"] ?? "#3a7d44" }}',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Kirim Sekarang',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'animated fadeInDown'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mengirim Email...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Submit form
                document.getElementById('resendForm-' + orderId).submit();
            }
        });
    }
</script>
@endsection