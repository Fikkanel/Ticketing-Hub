@extends('layouts.admin')

@section('title', 'Edit Lokasi: ' . $location->nama_lokasi)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-3">Formulir Edit Lokasi: {{ $location->nama_lokasi }}</h4>
            
            <form action="{{ route('admin.locations.update', $location->location_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama_lokasi" class="form-label">Nama Lokasi</label>
                    <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $location->nama_lokasi) }}" required>
                    @error('nama_lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $location->alamat) }}</textarea>
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="mb-3">
                    <label for="kota" class="form-label">Kota/Kabupaten</label>
                    <input type="text" class="form-control @error('kota') is-invalid @enderror" id="kota" name="kota" value="{{ old('kota', $location->kota) }}">
                    @error('kota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="map_link" class="form-label">Link Google Maps (Embed Source)</label>
                    <textarea class="form-control @error('map_link') is-invalid @enderror" id="map_link" name="map_link" rows="3" placeholder="Paste link src dari iframe Google Maps di sini (https://www.google.com/maps/embed?...)">{{ old('map_link', $location->map_link) }}</textarea>
                    <div class="form-text">Buka Google Maps -> Share -> Embed a map -> Copy HTML -> Ambil URL di dalam atribut src="..." saja.</div>
                    @error('map_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude (Opsional)</label>
                        <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $location->latitude) }}">
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude (Opsional)</label>
                        <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $location->longitude) }}">
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
