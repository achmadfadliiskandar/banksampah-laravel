@extends('layouts.pwa')

@section('content')
    <div class="mb-4">
        <h5 class="fw-bold">Riwayat Setoran</h5>
        <p class="text-muted small">Daftar aktivitas pengumpulan sampah kamu</p>
    </div>

    <div class="d-flex gap-2 mb-4 overflow-auto pb-2" style="white-space: nowrap;">
        <span class="badge rounded-pill bg-success px-3 py-2">Semua</span>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-2">Plastik</span>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-2">Kaca</span>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-2">Kertas</span>
    </div>

    <div class="row g-3">
        @forelse($transaksi as $t)
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold text-success">{{ $t->kode_transaksi }}</h6>
                                <small class="text-muted">{{ $t->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            @if($t->status == 'success')
                            <span class="badge bg-success-subtle text-success rounded-pill small px-3 text-uppercase fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Success
                            </span>
                        @elseif($t->status == 'pending')
                            <span class="badge bg-warning-subtle text-warning rounded-pill small px-3 text-uppercase fw-bold">
                                <i class="fas fa-clock me-1"></i> Pending
                            </span>
                        @elseif($t->status == 'cancelled')
                            <span class="badge bg-danger-subtle text-danger rounded-pill small px-3 text-uppercase fw-bold">
                                <i class="fas fa-times-circle me-1"></i> Cancelled
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small px-3 text-uppercase fw-bold">
                                {{ $t->status }}
                            </span>
                        @endif
                        </div>

                        <div class="my-2">
                            @foreach ($t->details as $detail)
                                <small class="badge bg-light text-dark border-0 fw-normal">
                                    {{ $detail->kategori->nama_jenis }} ({{ $detail->berat }}Kg)
                                    <br>
                                    <span>Harga Satuan:</span>  Rp {{ number_format($detail->kategori->harga_per_kg, 0, ',', '.') }}
                                </small>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <div>
                                <small class="text-muted d-block">Total Berat</small>
                                <span class="fw-bold">{{ number_format($t->total_berat, 2) }} Kg</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Total Pendapatan</small>
                                <span class="text-success fw-bold">Rp
                                    {{ number_format($t->total_harga, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                <p class="text-muted mt-2">Belum ada riwayat setoran.</p>
                <a href="{{ route('pwa.scan') }}" class="btn btn-success btn-sm rounded-pill px-4">Mulai Scan Sekarang</a>
            </div>
        @endforelse
    </div>

    <style>
        .bg-light-success {
            background-color: #d1e7dd;
        }

        .bg-light-warning {
            background-color: #fff3cd;
        }
    </style>
@endsection
