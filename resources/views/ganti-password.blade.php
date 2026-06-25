@extends('layouts.app') <!-- Sesuaikan dengan nama layout admin kamu -->

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ubah Password Keamanan</h1>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Ganti Password</h6>
                </div>
                <div class="card-body">
                    
                    <!-- Notifikasi Bawaan Bootstrap (Bisa diganti SweetAlert kalau ada) -->
                    @if(Session::has('success'))
                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                    @endif
                    @if(Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif

                    <form action="{{ route('admin.password.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label class="fw-bold">Password Saat Ini</label>
                            <input type="password" name="password_sekarang" class="form-control @error('password_sekarang') is-invalid @enderror" required>
                            @error('password_sekarang')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control @error('password_baru') is-invalid @enderror" required>
                            @error('password_baru')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="fw-bold">Ulangi Password Baru</label>
                            <input type="password" name="password_baru_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection