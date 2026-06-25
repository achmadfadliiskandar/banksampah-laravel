@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold text-success">Manajemen Nasabah</h3>
            <p class="text-muted">Kelola data profil, registrasi, dan proses penarikan saldo nasabah.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-success shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahNasabah">
                <i class="fas fa-user-plus me-2"></i> Tambah Nasabah Baru
            </button>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Table Section --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="150">Kode</th>
                            <th>Nama Nasabah</th>
                            <th>Email</th>
                            <th>Saldo</th>
                            <th class="text-center" width="250">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nasabah as $n)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-secondary px-2 py-1">{{ $n->kode_nasabah }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $n->name }}</div>
                            </td>
                            <td>{{ $n->email }}</td>
                            <td>
                                <span class="text-success fw-bold">Rp {{ number_format($n->saldo, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    {{-- +++ TOMBOL TOP UP SALDO (BARU) +++ --}}
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTopUp{{ $n->id }}" title="Top Up Saldo">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                    {{-- Tombol Tarik Saldo --}}
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalTarik{{ $n->id }}" title="Tarik Saldo">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </button>
                                    {{-- Tombol Reset Password --}}
                                    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalReset{{ $n->id }}" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    {{-- Tombol Hapus --}}
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus{{ $n->id }}" title="Hapus Nasabah">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- +++ MODAL TOP UP SALDO (BARU) +++ --}}
                        <div class="modal fade" id="modalTopUp{{ $n->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('users.topup-saldo', $n->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Top Up Saldo Nasabah</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="bg-light p-3 rounded mb-3 text-center">
                                                <small class="text-muted d-block">Saldo Saat Ini</small>
                                                <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($n->saldo, 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nominal Top Up</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success-subtle border-success text-success fw-bold">Rp</span>
                                                    <input type="number" name="nominal" class="form-control border-success" min="1000" required placeholder="Masukkan nominal pengisian">
                                                </div>
                                                <small class="text-muted">*Minimal Top Up: Rp 1.000</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success fw-bold px-4">Proses Top Up</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Reset Password --}}
                        <div class="modal fade" id="modalReset{{ $n->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('users.reset-password', $n->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-unlock-alt me-2"></i>Reset Password</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Ganti password untuk: <strong>{{ $n->name }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Password Baru</label>
                                                <input type="text" name="password" class="form-control" placeholder="Min. 6 karakter" required minlength="6">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-info text-white">Update Password</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Tarik Saldo --}}
                        <div class="modal fade" id="modalTarik{{ $n->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('users.tarik-saldo', $n->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Penarikan Saldo</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="bg-light p-3 rounded mb-3 text-center">
                                                <small class="text-muted d-block">Saldo Tersedia</small>
                                                <h4 class="fw-bold text-success mb-0">Rp {{ number_format($n->saldo, 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nominal Penarikan</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" name="nominal" class="form-control" min="1000" max="{{ $n->saldo }}" required placeholder="0">
                                                </div>
                                                <small class="text-muted">*Maksimal: Rp {{ number_format($n->saldo, 0, ',', '.') }}</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning fw-bold">Proses Tarik Saldo</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Hapus Nasabah --}}
                        <div class="modal fade" id="modalHapus{{ $n->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('users.destroy', $n->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-content border-0">
                                        <div class="modal-body text-center p-4">
                                            <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                                            <h5 class="fw-bold">Hapus Nasabah?</h5>
                                            <p class="text-muted">Data nasabah <strong>{{ $n->name }}</strong> beserta seluruh riwayat setorannya akan dihapus permanen.</p>
                                        </div>
                                        <div class="modal-footer border-0 d-flex justify-content-center pb-4">
                                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger px-4">Ya, Hapus</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Nasabah Baru (Diluar Loop) --}}
<div class="modal fade" id="modalTambahNasabah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Registrasi Nasabah Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama lengkap nasabah" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                        <small class="text-muted">Digunakan untuk login di aplikasi PWA.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Default</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">Simpan Nasabah</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection