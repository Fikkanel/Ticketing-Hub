@extends('layouts.public')

@section('title', 'Reset Password')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- Header --}}
            <div class="card-header text-center py-4" style="background: var(--primary-color);">
                <i class="fas fa-lock fa-3x text-white mb-2"></i>
                <h4 class="mb-0 text-white fw-bold">Reset Password</h4>
            </div>
            
            <div class="card-body p-4">
                {{-- Alert Messages --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-muted text-center mb-4">
                    Buat password baru untuk akun Anda.
                </p>

                <form action="{{ route('customer.password.update') }}" method="POST">
                    @csrf
                    
                    {{-- Hidden fields --}}
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 6 karakter"
                                   required 
                                   autofocus>
                        </div>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-bold">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ketik ulang password"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 py-3 fw-bold shadow-sm mb-3" style="background: var(--primary-color); color: #fff;">
                        <i class="fas fa-save me-2"></i> Simpan Password Baru
                    </button>
                    
                    <div class="text-center">
                        <a href="{{ route('customer.login') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
