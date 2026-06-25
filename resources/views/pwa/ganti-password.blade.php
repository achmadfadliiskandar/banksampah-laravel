@extends('layouts.pwa')

@section('content')
<div class="mb-4 text-center">
    <h4 class="fw-bold text-success">Ganti Password</h4>
    <p class="text-muted small">Amankan akun Anda secara berkala</p>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('pwa.password.update') }}" method="POST">
            @csrf

            <!-- Password Saat Ini -->
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Password Saat Ini</label>
                <div class="input-group mb-1">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                    <input type="password" name="password_sekarang" class="form-control border-start-0 bg-light @error('password_sekarang') is-invalid @enderror" placeholder="Masukkan password lama" required>
                </div>
                @error('password_sekarang')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <hr class="text-muted opacity-25 my-4">

            <!-- Password Baru -->
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Password Baru</label>
                <div class="input-group mb-1">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-success"></i></span>
                    <input type="password" name="password_baru" class="form-control border-start-0 bg-light @error('password_baru') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                </div>
                @error('password_baru')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">Ulangi Password Baru</label>
                <div class="input-group mb-1">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key-fill text-success"></i></span>
                    <input type="password" name="password_baru_confirmation" class="form-control border-start-0 bg-light" placeholder="Ketik ulang password baru" required>
                </div>
                <!-- Note: Tidak perlu error message khusus di sini karena otomatis ditangani oleh aturan 'confirmed' di kolom password_baru -->
            </div>

            <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-bold shadow-sm">
                <i class="bi bi-check2-circle me-2"></i> SIMPAN PASSWORD
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Menangkap pesan flash dari Session Laravel (Berhasil/Gagal)
    @if(Session::has('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ Session::get('success') }}',
            confirmButtonColor: '#198754'
        });
    @endif

    @if(Session::has('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ Session::get('error') }}',
            confirmButtonColor: '#dc3545'
        });
    @endif
</script>
@endsection