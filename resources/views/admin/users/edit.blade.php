@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Edit Admin</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="toggleEventSelect()">
                            @if(Auth::user()->isSuperAdmin())
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin Event</option>
                                <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                            @endif
                            <option value="scanner" {{ $user->role == 'scanner' ? 'selected' : '' }}>Scanner (Petugas QR)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="event_select_container">
                        <label for="event_ids" class="form-label">Kelola Event (Khusus Admin/Scanner)</label>
                        <select class="form-select @error('event_ids') is-invalid @enderror" id="event_ids" name="event_ids[]" multiple size="5">
                            @foreach($events as $event)
                                <option value="{{ $event->event_id }}" {{ in_array($event->event_id, old('event_ids', $user->events->pluck('event_id')->toArray())) ? 'selected' : '' }}>
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
                                // Don't clear values here to prevent accidental data loss on logic toggle
                            }
                        }
                        // Run on load
                        document.addEventListener('DOMContentLoaded', toggleEventSelect);
                    </script>

                    <hr>
                    <p class="text-muted small">Kosongkan jika tidak ingin mengubah password.</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
