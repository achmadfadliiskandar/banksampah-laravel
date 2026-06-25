@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h3 class="fw-bold text-success">Input Setoran Sampah</h3>
                <p class="text-muted">Catat setoran nasabah via manual/AI. Transaksi pending wajib diverifikasi fisik dalam 2 hari atau dihapus jika fiktif.</p>
            </div>
        </div>

        {{-- NOTIFIKASI SYSTEM --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- 🔥 TAMBAHKAN WADAH INI AGAR SINKRON DENGAN KIRIMAN CONTROLLER SCAN AI --}}
        @if (session('success_ai'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm border-start border-info border-4" role="alert">
                <strong>🤖 BINGO!</strong> {{ session('success_ai') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-start border-warning border-4" role="alert">
                <strong>⚠️ PERINGATAN AI:</strong> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- KOLOM KIRI: FORM INPUT (AI & MANUAL) --}}
            <div class="col-md-4">
                {{-- CARD SCAN AI --}}
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-robot text-primary me-2"></i>Scan AI Otomatis</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transaksi.scan-ai') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group mb-2">
                                <input type="file" name="foto_sampah" class="form-control" accept="image/*" required>
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Analisis</button>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle"></i> Upload foto sampah, AI akan otomatis memilihkan kategorinya.</small>
                        </form>
                    </div>
                </div>

                {{-- FORM SETORAN BARU --}}
                <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-success me-2"></i>Form Setoran Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('transaksi.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold mb-1">Nasabah</label>
                                <input type="text" id="nasabahInput" class="form-control" placeholder="Ketik nama nasabah..." list="dataNasabah" required autocomplete="off">
                                <datalist id="dataNasabah">
                                    @foreach ($nasabah as $n)
                                        <option value="{{ $n->name }}" data-id="{{ $n->id }}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="user_id" id="userIdHidden" required>
                            </div>

                            <div id="item-container">
                                <label class="fw-bold mb-1">Rincian Sampah</label>
                                <div class="row item-row mb-2">
                                    <div class="col-md-6 pe-1">
                                        <select name="kategori_id[]" class="form-control" required>
                                            <option value="">-- Kategori --</option>
                                            @foreach ($kategori as $k)
                                                <option value="{{ $k->id }}" {{ session('auto_select_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_jenis }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 px-1">
                                        <input type="number" step="0.01" name="berat[]" class="form-control" placeholder="Berat (Kg)" required>
                                    </div>
                                    <div class="col-md-2 ps-1">
                                        <button type="button" class="btn btn-danger w-100 remove-row"><i class="fas fa-minus"></i></button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="add-item" class="btn btn-outline-secondary btn-sm mb-4 w-100"><i class="fas fa-plus"></i> Tambah Jenis</button>
                            <button type="submit" class="btn btn-success w-100 fw-bold py-2"><i class="fas fa-save me-1"></i> Simpan Transaksi</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: TABEL RIWAYAT TRANSAKSI --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-history text-secondary me-2"></i>Riwayat Transaksi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Kode TRX</th>
                                        <th>Nasabah</th>
                                        <th>Rincian Sampah</th>
                                        <th>Total Berat</th>
                                        <th>Total Bayar</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($setoran as $s)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark">{{ $s->kode_transaksi }}</div>
                                                <small class="text-muted">{{ $s->created_at->format('d M Y, H:i') }}</small>
                                                <br>
                                                <span class="badge {{ $s->status == 'success' ? 'bg-success' : 'bg-warning text-dark' }} mt-1">Status: {{ ucfirst($s->status) }}</span>
                                            </td>
                                            <td><div class="fw-bold">{{ $s->user->name }}</div></td>
                                            <td>
                                                @foreach ($s->details as $detail)
                                                    <span class="badge bg-light text-dark border mb-1">
                                                        {{ $detail->kategori->nama_jenis }} ({{ $detail->berat }} Kg)
                                                    </span><br>
                                                @endforeach
                                            </td>
                                            <td>{{ $s->total_berat }} Kg</td>
                                            <td class="fw-bold text-success">Rp {{ number_format($s->total_harga, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php
                                                    $selisihHari = \Carbon\Carbon::parse($s->created_at)->diffInDays(\Carbon\Carbon::now());
                                                    $wajibDihapus = $s->status == 'pending' && $selisihHari >= 2;
                                                @endphp

                                                <div class="p-1 rounded-3 {{ $wajibDihapus ? 'border border-danger border-2 bg-danger-subtle' : '' }}">
                                                    @if ($wajibDihapus)
                                                        <form action="{{ route('transaksi.destroy', $s->id) }}" method="POST">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm" onclick="return confirm('Hapus data fiktif?')">
                                                                <i class="fas fa-trash-alt"></i> Bersihkan Data Fiktif
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- ====== TOMBOL AKSI GABUNGAN MURNI BOOTSTRAP 5 ====== --}}
                                                        <div class="d-flex gap-1 justify-content-center">
                                                            
                                                            {{-- TOMBOL HIJAU VERIFIKASI (ID DISINKRONKAN: #modalSetujuTrx-) --}}
                                                            @if ($s->status == 'pending')
                                                                <button type="button" class="btn btn-sm btn-success fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSetujuTrx-{{ $s->id }}">
                                                                    <i class="fas fa-check-circle"></i> Verifikasi
                                                                </button>
                                                            @endif

                                                            {{-- DROPDOWN MENU UTAMA --}}
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm {{ $s->status == 'success' ? 'btn-success text-white' : 'btn-secondary' }} dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-cog"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                                                                    {{-- KOREKSI TIMBUGAN SEKALIGUS LIHAT DETAIL --}}
                                                                    <li>
                                                                        <button class="dropdown-item text-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalDetailTrx-{{ $s->id }}">
                                                                            <i class="fas fa-edit fa-fw me-2"></i> Koreksi & Detail Sampah
                                                                        </button>
                                                                    </li>

                                                                    @if ($s->status == 'pending')
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li>
                                                                            <form action="{{ route('transaksi.destroy', $s->id) }}" method="POST" id="form-tolak-{{ $s->id }}">
                                                                                @csrf @method('DELETE')
                                                                                <button type="button" class="dropdown-item text-danger" onclick="if(confirm('Tolak & hapus transaksi ini?')) document.getElementById('form-tolak-{{ $s->id }}').submit();">
                                                                                    <i class="fas fa-times fa-fw me-2"></i> Tolak & Hapus Data
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @elseif ($s->status == 'success' && $selisihHari < 2)
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li>
                                                                            <form action="{{ route('transaksi.destroy', $s->id) }}" method="POST" id="form-batal-{{ $s->id }}">
                                                                                @csrf @method('DELETE')
                                                                                <button type="button" class="dropdown-item text-danger" onclick="if(confirm('Batalkan transaksi ini? Saldo nasabah akan dipotong kembali.')) document.getElementById('form-batal-{{ $s->id }}').submit();">
                                                                                    <i class="fas fa-undo fa-fw me-2"></i> Batalkan & Potong Saldo
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">Belum ada transaksi setoran.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 🔥 KUMPULAN MODAL UTUH (DI KAKI HALAMAN, ID DIKUNCI MATI AGAR TIDAK LOCK) --}}
    {{-- ========================================================================= --}}
    @foreach($setoran as $s)
        
        {{-- 1. MODAL DETAIL TRANSAKSI & KOREKSI TABEL INPUT (STRUKTUR DESIMAL TERKUNCI) --}}
        <div class="modal fade" id="modalDetailTrx-{{ $s->id }}" tabindex="-1" aria-labelledby="modalDetailLabel-{{ $s->id }}" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fw-bold" id="modalDetailLabel-{{ $s->id }}">
                            <i class="fas fa-edit text-warning me-2"></i> Koreksi Item {{ $s->kode_transaksi }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <form action="{{ route('transaksi.update', $s->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="modal-body p-3 text-dark">
                            <div class="card card-body bg-light border-0 shadow-sm mb-3 py-2">
                                <div class="row small">
                                    <div class="col-md-6">
                                        <strong>Nama Nasabah:</strong> <span class="text-secondary">{{ $s->user->name }}</span>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <strong>Waktu Rekam:</strong> <span class="text-secondary">{{ $s->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-table me-1"></i> Tabel Timbangan Fisik</h6>
                            
                            @if ($s->details->count() > 0)
                                <div class="table-responsive rounded shadow-sm mb-3">
                                    <table class="table table-bordered table-hover align-middle mb-0 text-center small">
                                        <thead class="table-secondary text-dark fw-bold">
                                            <tr>
                                                <th style="width: 20%">Foto Sampah</th>
                                                <th style="width: 50%">Pilihan Kategori (kategori_id)</th>
                                                <th style="width: 30%">Berat Aktual (berat)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($s->details as $detail)
                                                <tr>
                                                    <td>
                                                        @if(!empty($detail->path_foto) && \Storage::disk('public')->exists($detail->path_foto))
                                                            <img src="{{ asset('storage/' . $detail->path_foto) }}" class="img-thumbnail rounded shadow-sm" style="max-width: 75px; max-height: 75px; object-fit: cover;">
                                                        @else
                                                            <div class="text-muted fst-italic" style="font-size: 0.75rem;">
                                                                <i class="fas fa-image-slash d-block mb-1 fa-lg"></i> No Image
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-start">
                                                        <input type="hidden" name="detail_id[]" value="{{ $detail->id }}">
                                                        <select name="kategori_id[]" class="form-select form-select-sm fw-semibold text-secondary" required>
                                                            @foreach ($kategori as $k)
                                                                <option value="{{ $k->id }}" {{ $detail->kategori_id == $k->id ? 'selected' : '' }}>
                                                                    {{ $k->nama_jenis }} (Rp {{ number_format($k->harga_per_kg, 0, ',', '.') }}/Kg)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" step="0.01" name="berat[]" class="form-control text-center fw-bold" value="{{ number_format($detail->berat, 2, '.', '') }}" min="0.01" required>
                                                            <span class="input-group-text bg-secondary text-white">Kg</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-danger text-center mb-0">Rincian data sampah kosong.</div>
                            @endif
                        </div>
                        
                        <div class="modal-footer bg-light d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm flex-grow-1 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                            @if ($s->details->count() > 0 && \Carbon\Carbon::parse($s->created_at)->diffInDays(now()) <= 2)
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold shadow-sm"><i class="fas fa-calculator me-1"></i> Simpan & Hitung Ulang</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. MODAL VERIFIKASI SETUJU (ID MAJU DIKUNCI PASTI SINKRON: id="modalSetujuTrx-...") --}}
        @if ($s->status == 'pending')
            <div class="modal fade" id="modalSetujuTrx-{{ $s->id }}" tabindex="-1" aria-labelledby="modalSetujuLabel-{{ $s->id }}" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold" id="modalSetujuLabel-{{ $s->id }}"><i class="fas fa-check-double me-2"></i> Konfirmasi Validasi Fisik</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-center text-dark">
                            <div class="text-success mb-3"><i class="fas fa-balance-scale fa-4x"></i></div>
                            <h5 class="fw-bold mb-2">Validasi Kode {{ $s->kode_transaksi }}</h5>
                            <p class="text-muted small">Pastikan fisik sampah yang dibawa nasabah <strong>{{ $s->user->name }}</strong> sudah sesuai dengan timbangan rincian di sistem.</p>
                            <div class="alert alert-secondary text-start small mb-0 py-2">
                                <i class="fas fa-info-circle me-1 text-primary"></i> Status transaksi akan berubah menjadi <span class="badge bg-success">Success</span> dan saldo nasabah akan bertambah otomatis.
                            </div>
                        </div>
                        <div class="modal-footer bg-light d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm flex-grow-1 fw-semibold" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('transaksi.update-status', $s->id) }}" method="POST" class="flex-grow-1 p-0 m-0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="success">
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold text-white shadow-sm"><i class="fas fa-check me-1"></i> Ya, Sudah Sesuai</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- LOGIKA JAVASCRIPT STANDAR FORM --}}
    <script>
        document.getElementById('add-item').addEventListener('click', function() {
            let container = document.getElementById('item-container');
            let row = container.querySelector('.item-row').cloneNode(true);
            row.querySelector('input').value = '';
            row.querySelector('select').selectedIndex = 0;
            container.appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                if (document.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('.item-row').remove();
                }
            }
        });

        document.getElementById('nasabahInput').addEventListener('input', function() {
            const inputVal = this.value;
            const options = document.querySelectorAll('#dataNasabah option');
            const hiddenInput = document.getElementById('userIdHidden');
            hiddenInput.value = "";
            options.forEach(function(option) {
                if (option.value === inputVal) hiddenInput.value = option.getAttribute('data-id');
            });
        });
    </script>
@endsection