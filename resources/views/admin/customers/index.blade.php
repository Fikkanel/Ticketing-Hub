@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                <h6 class="m-0 fw-bold text-primary">Manajemen Customer</h6>
                
                <form action="{{ route('admin.customers.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="Cari ID, nama, email, hp..." aria-label="Search" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">ID Customer</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Total Order</th>
                                <th>Terdaftar Sejak</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td><code class="text-primary">{{ $customer->unix_id ?? 'TIX' . str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</code></td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info text-white rounded-pill px-3">{{ $customer->orders_count }}</span>
                                </td>
                                <td>{{ $customer->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-primary rounded-circle shadow-sm" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2 text-gray-300"></i>
                                    <p class="mb-0">Tidak ada data customer ditemukan.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination yang rapi --}}
                <div class="d-flex justify-content-end mt-4">
                    {{ $customers->appends(['search' => request('search')])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
