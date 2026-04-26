@extends('layouts.admin')

@section('title', 'Manajemen Banner')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">Manajemen Banner</h6>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Banner
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="150">Preview</th>
                            <th>Judul / Info</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $index => $banner)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <img src="{{ $banner->banner_src }}" alt="Banner" class="img-fluid rounded" style="max-height: 50px;">
                                </td>
                                <td>{{ $banner->title ?? '-' }}</td>
                                <td>
                                    @if($banner->url)
                                        <a href="{{ $banner->url }}" target="_blank" class="small">{{ Str::limit($banner->url, 30) }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($banner->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>{{ $banner->order }}</td>
                                <td>
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus banner ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Belum ada banner yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
