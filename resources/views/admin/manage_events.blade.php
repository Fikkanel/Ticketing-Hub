@extends('layouts.admin')

@section('title', 'Daftar Event')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">Semua Event Terdaftar</h6>
            @if(Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Tambah Event Baru
                </a>
            @endif
        </div>
        <div class="card-body">
            @if($events->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                    <p>Belum ada event. Mulai buat event pertamamu!</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 30%">Judul Event</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th class="text-center">Status</th>
                                <th style="width: 15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $event->judul }}</td>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-danger me-1 small"></i> 
                                        {{ $event->location->nama_lokasi ?? '-' }}
                                    </td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($event->tgl_mulai)->format('d M Y') }}</td>
                                    <td class="text-center">
                                        @if($event->status == 'Active')
                                            <span class="badge bg-success rounded-pill px-3">Active</span>
                                        @elseif($event->status == 'Upcoming')
                                            <span class="badge bg-primary rounded-pill px-3">Upcoming</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3">Finished</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.events.edit', $event->event_id) }}" class="btn btn-warning btn-sm btn-circle" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="{{ route('admin.bundles.index', $event->event_id) }}" class="btn btn-primary btn-sm btn-circle" title="Bundling Tiket">
                                            <i class="fas fa-boxes"></i>
                                        </a>
                                        @if($event->layout_type === 'manual_map')
                                            <a href="{{ route('admin.events.venue_map', $event->event_id) }}" class="btn btn-info btn-sm btn-circle" title="Venue Map Builder">
                                                <i class="fas fa-map"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.events.delete', $event->event_id) }}" method="POST" class="d-inline" id="delete-event-{{ $event->event_id }}">
                                            @csrf
                                            @method('DELETE') 
                                            <button type="button" class="btn btn-danger btn-sm btn-circle" 
                                                    onclick="confirmAction(event, 'Hapus Event?', 'Anda yakin ingin menghapus event &quot;{{ $event->judul }}&quot;?', 'delete-event-{{ $event->event_id }}')" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
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
