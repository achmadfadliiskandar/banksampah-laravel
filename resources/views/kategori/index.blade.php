@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-success">Master Kategori Sampah</h3>
                <p class="text-muted">Kelola jenis sampah, kode label AI, dan harga pasar terbaru.</p>
            </div>
            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-2"></i>Tambah Jenis Sampah
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger"
                style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Peringatan Sistem:</strong> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Kode (Label)</th>
                                <th>Nama Jenis</th>
                                <th>Tipe</th>
                                <th>Harga / Kg</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kategori as $item)
                                <tr>
                                    <td class="ps-4">
                                        <code
                                            class="bg-light px-2 py-1 border rounded text-danger">{{ $item->kode_kategori }}</code>
                                    </td>
                                    <td class="fw-bold">{{ $item->nama_jenis }}</td>
                                    <td>
                                        @if ($item->tipe == 'organik')
                                            <span class="badge bg-success-subtle text-success px-3">ORGANIK</span>
                                        @elseif($item->tipe == 'anorganik')
                                            <span class="badge bg-info-subtle text-info px-3">ANORGANIK</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-3">B3</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark">Rp {{ number_format($item->harga_per_kg, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $item->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('kategori.destroy', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Hapus kategori ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('kategori.update', $item->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="alert"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Jenis</label>
                                                        <input type="text" name="nama_jenis" class="form-control"
                                                            value="{{ $item->nama_jenis }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipe Sampah</label>
                                                        <select name="tipe" class="form-select">
                                                            <option value="organik"
                                                                {{ $item->tipe == 'organik' ? 'selected' : '' }}>Organik
                                                            </option>
                                                            <option value="anorganik"
                                                                {{ $item->tipe == 'anorganik' ? 'selected' : '' }}>
                                                                Anorganik</option>
                                                            <option value="b3"
                                                                {{ $item->tipe == 'b3' ? 'selected' : '' }}>B3</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga per Kg</label>
                                                        <input type="number" name="harga_per_kg" class="form-control"
                                                            value="{{ $item->harga_per_kg }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light"
                                                        data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning">Update Data</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada data kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('kategori.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Kategori (Sesuai Label)</label>
                            <select name="kode_kategori" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Kode Kategori --</option>
                                <option value="cardboard">cardboard</option>
                                <option value="glass">glass</option>
                                <option value="metal">metal</option>
                                <option value="paper">paper</option>
                                <option value="plastic">plastic</option>
                                <option value="trash">trash</option>
                            </select>
                            <small class="text-muted italic">*Gunakan huruf kecil dan underscore</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Jenis Sampah</label>
                            <input type="text" name="nama_jenis" class="form-control"
                                placeholder="Contoh: Botol Plastik PET" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Sampah</label>
                            <select name="tipe" class="form-select">
                                <option value="organik">Organik</option>
                                <option value="anorganik">Anorganik</option>
                                <option value="b3">B3 (Berbahaya)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga per Kg</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_per_kg" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Kategori</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
