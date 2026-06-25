@extends('layouts.pwa')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <small class="text-muted">Selamat datang,</small>
            <h5 class="fw-bold mb-0">{{ Auth::user()->name }}</h5>
        </div>
        <div class="dropdown">
            <a href="#" class="text-dark" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li>
                    <a class="dropdown-item small d-flex align-items-center" href="{{ url('pwa/profil/ganti-password') }}">
                        <i class="bi bi-shield-lock me-2 text-success"></i> Ubah Password
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card bg-success text-white mb-4 shadow-lg" style="background: linear-gradient(45deg, #198754, #20c997);">
        <div class="card-body p-4">
            <small class="opacity-75">Total Saldo Tabungan</small>
            <h2 class="fw-bold mb-3">Rp {{ number_format($totalTabungan, 0, ',', '.') }}</h2>
            <div class="d-flex justify-content-between align-items-center bg-white bg-opacity-25 rounded-pill px-3 py-2">
                <small><i class="bi bi-recycle me-1"></i> Total Sampah:
                    <strong>{{ number_format($totalSampah, 1, ',', '.') }} Kg</strong></small>
                <a href="{{ route('pwa.riwayat') }}" class="text-white text-decoration-none small fw-bold">Detail ></a>
            </div>
        </div>
    </div>

    {{-- Menu Panduan & Lokasi dengan Pemicu Modal (Data-BS-Toggle) --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card text-center p-3 h-100 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPanduan"
                style="cursor: pointer;">
                <div class="text-primary mb-2"><i class="bi bi-info-circle fs-3"></i></div>
                <h6 class="small fw-bold mb-1">Panduan</h6>
                <p class="text-muted mb-0" style="font-size: 10px;">Cara memilah sampah</p>
            </div>
        </div>
        <div class="col-6">
            <div class="card text-center p-3 h-100 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalLokasi"
                style="cursor: pointer;">
                <div class="text-warning mb-2"><i class="bi bi-geo-alt fs-3"></i></div>
                <h6 class="small fw-bold mb-1">Lokasi</h6>
                <p class="text-muted mb-0" style="font-size: 10px;">Cari bank sampah</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Aktivitas Terakhir</h6>
        <a href="{{ route('pwa.riwayat') }}" class="text-success small text-decoration-none">Lihat Semua</a>
    </div>

    <div class="list-group list-group-flush">
        @forelse($riwayatTerbaru as $setoran)
            <div class="list-group-item d-flex align-items-center py-3">
                <div class="bg-light p-2 rounded-circle me-3">
                    <i class="bi bi-arrow-down-left text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0 small fw-bold">
                        {{-- Mengambil nama kategori sampah pertama dalam transaksi ini --}}
                        Setoran {{ $setoran->details->first()->kategori->nama_jenis ?? 'Sampah' }}
                        @if ($setoran->details->count() > 1)
                            <small class="text-muted">(+{{ $setoran->details->count() - 1 }} lainnya)</small>
                        @endif
                    </h6>
                    <small class="text-muted" style="font-size: 11px;">
                        {{ $setoran->created_at->format('d M Y') }} • {{ number_format($setoran->total_berat, 1) }} Kg
                    </small>
                </div>
                <div class="text-end">
                    <span class="text-success small fw-bold">
                        +Rp {{ number_format($setoran->total_harga, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <small class="text-muted">Belum ada aktivitas setoran.</small>
            </div>
        @endforelse
    </div>


    {{-- ========================================================================= --}}
    {{-- 1. STRUKTUR MODAL PANDUAN PEMILAHAN SAMPAH --}}
    {{-- ========================================================================= --}}
    <div class="modal fade" id="modalPanduan" tabindex="-1" aria-labelledby="modalPanduanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold" id="modalPanduanLabel"><i class="bi bi-book me-2"></i>Panduan Setoran
                        Sampah</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body small text-dark">
                    <div class="text-center mb-3">
                        <i class="bi bi-stars text-warning fs-1"></i>
                        <h6 class="fw-bold text-primary mt-1">Bisa Menggunakan Fitur Scan AI / Datang Langsung Ke Lokasi
                        </h6>
                    </div>
                    <ol class="ps-3 mb-3">
                        <li class="mb-2"><strong>Buka Menu Scan:</strong> Ambil foto botol plastik, kaca, kertas, kardus,
                            logam, trash menggunakan kamera PWA dari mana saja.</li>
                        <li class="mb-2"><strong>Dapatkan Estimasi:</strong> Sistem *Deep Learning* MobileNetV2 akan
                            otomatis menebak jenis dan mencatat estimasi saldo tabunganmu.</li>
                        <li class="mb-2"><strong>Simpan Setoran:</strong> Klik simpan setoran agar data tersimpan di
                            sistem dengan status *Pending*.</li>
                        <li class="mb-2"><strong>Bawa Fisik Sampah:</strong> Bawa botol plastik asli ke kantor pusat Bank
                            Sampah dalam kurun waktu **maksimal 2 hari**.</li>
                        <li class="mb-2"><strong>Keterangan:</strong> Jika Ingin Datang Langsung tanpa/tidak melakukan
                            scan langsung di aplikasi ini , silahkan datang saja ke lokasi, Jika ingin melakukan scan ikuti
                            langkah(1-4).</li>
                    </ol>
                    <div class="alert alert-warning border-0 small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Aturan Keamanan:</strong> Jika dalam
                        waktu 2 hari fisik sampah tidak diserahkan ke admin loket, data transaksi dianggap fiktif dan
                        otomatis dibersihkan oleh admin/sistem!
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary w-100 fw-bold" data-bs-dismiss="modal">Saya
                        Mengerti</button>
                </div>
            </div>
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- 2. STRUKTUR MODAL LOKASI KANTOR BANK SAMPAH --}}
    {{-- ========================================================================= --}}
    <div class="modal fade" id="modalLokasi" tabindex="-1" aria-labelledby="modalLokasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold" id="modalLokasiLabel"><i class="bi bi-geo-alt-fill me-2"></i>Lokasi
                        Kantor Pusat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body small text-dark">
                    <div class="bg-light p-3 rounded mb-3">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-building me-1 text-success"></i> Kantor Utama
                            Bank Sampah</h6>
                        <p class="text-muted mb-2" style="font-size: 11px;">Jl. Nusantara Raya No 27 Depok</p>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span><i class="bi bi-clock me-1"></i> Jam Operasional:</span>
                            <span class="fw-bold text-dark">08:00 - 16:00 WIB</span>
                        </div>
                    </div>

                    {{-- Template Peta Interaktif (Menggunakan Placeholder Visual Klasik untuk PWA Mobile) --}}
                    {{-- Tinggi dinaikkan menjadi 250px agar peta terlihat lebih luas dan proporsional --}}
                    <div class="card bg-dark text-white border-0 overflow-hidden position-relative rounded"
                        style="height: 250px;">

                        {{-- Iframe Google Maps Diatur agar Mengisi 100% Kotak Pembungkus --}}
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.132470764491!2d106.824147!3d-6.376918!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMjInMzYuOSJTIDEwNsKwNDknMjYuOSJF!5e0!3m2!1sid!2sid!4v1716612345678!5m2!1sid!2sid"
                            class="w-100 h-100 position-absolute top-0 start-0" style="border:0; z-index: 2;"
                            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                        </iframe>

                        {{-- Layer Placeholder Loading / Cadangan jika internet lambat --}}
                        <div class="w-100 h-100 bg-secondary d-flex flex-column align-items-center justify-content-center text-center p-3 opacity-75 position-absolute top-0 start-0"
                            style="z-index: 1;">
                            <i class="bi bi-map-fill fs-2 mb-1 text-warning animate-pulse"></i>
                            <span class="fw-bold" style="font-size: 11px;">Memuat Google Maps Lokasi...</span>
                        </div>

                    </div>

                    <p class="text-muted text-center mt-2 mb-0" style="font-size: 10px;">*Tunjukkan halaman bukti
                        transaksi PWA ke petugas loket saat menimbang sampah fisik.</p>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary w-100 fw-bold"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
