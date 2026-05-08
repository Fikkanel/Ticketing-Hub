@extends('layouts.admin')

@section('title', 'Manajemen Lokasi')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
            <h6 class="m-0 fw-bold text-primary">Daftar Semua Lokasi</h6>
            <a href="{{ route('admin.locations.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus fa-sm me-1"></i> Tambah Lokasi Baru
            </a>
        </div>
        <div class="card-body">
            @if($locations->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-map-marker-alt fa-3x mb-3 opacity-50"></i>
                    <p>Belum ada data lokasi yang dimasukkan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 25%">Nama Lokasi</th>
                                <th>Alamat</th>
                                <th style="width: 15%">Kota</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $location)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $location->nama_lokasi }}</td>
                                    <td>{{ $location->alamat }}</td>
                                    <td>{{ $location->kota }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.locations.edit', $location->location_id) }}" class="btn btn-sm btn-warning mb-1 mb-lg-0">Edit</a>
                                        
                                        <form action="{{ route('admin.locations.destroy', $location->location_id) }}" method="POST" class="d-inline" id="delete-location-{{ $location->location_id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmAction(event, 'Hapus Lokasi?', 'Yakin ingin menghapus {{ $location->nama_lokasi }}?', 'delete-location-{{ $location->location_id }}')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
