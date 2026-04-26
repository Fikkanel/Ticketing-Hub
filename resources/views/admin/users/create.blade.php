@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Tambah Admin Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="toggleEventSelect()">
                            @if(Auth::user()->isSuperAdmin())
                                <option value="admin">Admin Event</option>
                                <option value="superadmin">Superadmin</option>
                            @endif
                            <option value="scanner">Scanner (Petugas QR)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="event_select_container">
                        <label for="event_ids" class="form-label">Kelola Event (Khusus Admin/Scanner)</label>
                        <select class="form-select @error('event_ids') is-invalid @enderror" id="event_ids" name="event_ids[]" multiple size="5">
                            @foreach($events as $event)
                                <option value="{{ $event->event_id }}" {{ in_array($event->event_id, old('event_ids', [])) ? 'selected' : '' }}>
                                    {{ $event->judul }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Tahan tombol CTRL (atau Command di Mac) untuk memilih lebih dari satu event.</div>
                        @error('event_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <script>
                        function toggleEventSelect() {
                            var role = document.getElementById('role').value;
                            if (role === 'admin' || role === 'scanner') {
                                eventSelect.style.display = 'block';
                            } else {
                                eventSelect.style.display = 'none';
                                // Clear selection if hidden? verifying logic not strictly needed if controller handles it
                            }
                        }
                        // Run on load
                        document.addEventListener('DOMContentLoaded', toggleEventSelect);
                    </script>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
