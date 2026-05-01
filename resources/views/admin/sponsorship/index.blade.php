@extends('layouts.admin')

@section('title', 'Manajemen Sponsorship')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Sponsorship</h1>
        <a href="{{ route('admin.sponsorships.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Generate Sponsorship Tickets
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Sponsorship</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Sponsor</th>
                            <th>Event</th>
                            <th>Jumlah Tiket</th>
                            <th>Order ID</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsorships as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->event->judul }}</td>
                                <td>{{ number_format($s->quota) }}</td>
                                <td><code>#{{ $s->order_id }}</code></td>
                                <td>{{ $s->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.sponsorships.export', $s->id) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv"></i> Export Links
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data sponsorship.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $sponsorships->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
