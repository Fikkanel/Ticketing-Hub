@extends('layouts.public')

@section('title', 'Lupa Password')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- Header --}}
            <div class="card-header text-center py-4" style="background: var(--primary-color);">
                <i class="fas fa-key fa-3x text-white mb-2"></i>
                <h4 class="mb-0 text-white fw-bold">Lupa Password?</h4>
            </div>
            
            <div class="card-body p-4">
                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
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
                    Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password.
                </p>

                <form action="{{ route('customer.password.email') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" 
                                   class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="nama@email.com"
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 py-3 fw-bold shadow-sm mb-3" style="background: var(--primary-color); color: #fff;">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Link Reset Password
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
