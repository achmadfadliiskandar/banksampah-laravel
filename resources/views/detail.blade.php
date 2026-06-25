@extends('layouts.app')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">

@section('content')
    <div class="container-fluid p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-light border shadow-sm mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <h2 class="fw-bold mb-0 text-gray-800">{{ $title }}</h2>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <div class="table-responsive">
                <table id="tabelKategoriSampah" class="table table-hover align-middle" style="width:100%">
                    
                    <thead class="@if($tipe === 'nasabah') table-primary @elseif($tipe === 'sampah') table-success @elseif($tipe === 'tabungan') table-warning text-dark @endif">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Nasabah</th>
                            <th>Jumlah Sampah</th>
                            <th>Nominal</th>
                            <th>Penarikan</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @foreach ($dataList as $index => $nasabah)
                            @php
                                $kgSampah = $nasabah->total_berat_sampah ?? 0;
                                $nominalKotor = $nasabah->total_pemasukan ?? 0;
                                $saldoAktif = $nasabah->saldo ?? 0;
                                $totalPenarikan = $nominalKotor - $saldoAktif;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                {{-- 🔥 1. TAMBAH CLASS 'kolom-nama' --}}
                                <td class="fw-bold text-secondary kolom-nama">{{ $nasabah->name }}</td>
                                
                                {{-- 🔥 2. TAMBAH CLASS 'kolom-berat' --}}
                                <td class="fw-bold text-dark kolom-berat">{{ number_format($kgSampah, 1, ',', '.') }} Kg</td>
                                
                                {{-- 🔥 3. TAMBAH CLASS 'kolom-nominal' --}}
                                <td class="kolom-nominal">Rp {{ number_format($nominalKotor, 0, ',', '.') }}</td>
                                
                                {{-- 🔥 4. TAMBAH CLASS 'kolom-penarikan' --}}
                                <td class="text-danger kolom-penarikan">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</td>
                                
                                {{-- 🔥 5. TAMBAH CLASS 'kolom-saldo' --}}
                                <td class="text-success fw-bold kolom-saldo">Rp {{ number_format($saldoAktif, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    
                    {{-- 🔥 6. BERIKAN ID UNTUK TIAP TH DI TFOOT AGAR BISA DIEDIT DINAMIS OLEH JS --}}
                    <tfoot class="table-light">
                        <tr>
                            <th class="text-dark fw-bold" id="total-nasabah-id">Total Nasabah : {{ $dataList->count() }}</th>
                            <th class="text-end fw-bold">Total Rekapitulasi Keseluruhan:</th>
                            <th class="text-dark fw-bold" id="total-berat-id">0,0 Kg</th>
                            <th class="text-dark fw-bold" id="total-nominal-id">Rp 0</th>
                            <th class="text-danger fw-bold" id="total-penarikan-id">Rp 0</th>
                            <th class="text-success fw-bold" id="total-saldo-id">Rp 0</th>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>

<script>
    new DataTable('#tabelKategoriSampah', {
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            processing:   "Sedang memproses...",
            lengthMenu:   "Tampilkan _MENU_ data",
            zeroRecords:  "Tidak ditemukan data yang sesuai",
            info:         "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty:    "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search:       "Cari Data:",
            paginate: {
                first:    "Pertama",
                previous: "Sebelumnya",
                next:     "Selanjutnya",
                last:     "Terakhir"
            }
        },
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            
            // Set anti-duplikat untuk menghitung jumlah user unik
            var kumpulanNamaUnik = new Set();
            
            var totalBerat = 0;
            var totalNominal = 0;
            var totalPenarikan = 0;
            var totalSaldo = 0;

            // Fungsi pembantu super kuat untuk membersihkan format uang/berat Indonesia ke angka murni
            function bersihkanAngkaIndonesia(stringMentah) {
                if (!stringMentah) return 0;
                
                var bersih = stringMentah.innerText.trim();
                // 1. Buang tulisan 'Rp', 'Kg', dan spasi kosong
                bersih = bersih.replace(/Rp|Kg|\s/g, '');
                // 2. Ubah titik ribuan (cth: 38.500 menjadi 38500) agar tidak dianggap desimal oleh JS
                bersih = bersih.split('.').join('');
                // 3. Ubah koma desimal Indonesia menjadi titik desimal standar komputer (cth: 22,5 menjadi 22.5)
                bersih = bersih.replace(',', '.');
                
                return parseFloat(bersih) || 0;
            }

            // Loop hanya pada baris yang lolos saringan pencarian (terfilter di layar)
            api.rows({ search: 'applied' }).every(function() {
                var rowNode = this.node();
                
                // 1. Ambil Nama & hitung keunikan user
                var elNama = rowNode.querySelector('.kolom-nama');
                if (elNama) {
                    var nama = elNama.innerText.trim();
                    if (nama) kumpulanNamaUnik.add(nama);
                }

                // 2. Kalkulasi Total Berat Kg
                var elBerat = rowNode.querySelector('.kolom-berat');
                if (elBerat) {
                    totalBerat += bersihkanAngkaIndonesia(elBerat);
                }

                // 3. Kalkulasi Total Nominal Kotor
                var elNominal = rowNode.querySelector('.kolom-nominal');
                if (elNominal) {
                    totalNominal += bersihkanAngkaIndonesia(elNominal);
                }

                // 4. Kalkulasi Total Penarikan Uang
                var elPenarikan = rowNode.querySelector('.kolom-penarikan');
                if (elPenarikan) {
                    totalPenarikan += bersihkanAngkaIndonesia(elPenarikan);
                }

                // 5. Kalkulasi Sisa Saldo Aktif
                var elSaldo = rowNode.querySelector('.kolom-saldo');
                if (elSaldo) {
                    totalSaldo += bersihkanAngkaIndonesia(elSaldo);
                }
            });

            // 🔥 SUNTIKKAN NILAI BARU KE TFOOT (DENGAN PENGUNCI NOL FORMAT AKUNTANSI)
            document.getElementById('total-nasabah-id').innerText = "Total Nasabah : " + kumpulanNamaUnik.size;
            
            // Format Berat (Kg) balikkan dari titik ke koma desimal Indonesia
            document.getElementById('total-berat-id').innerText = totalBerat.toFixed(1).replace('.', ',') + " Kg";
            
            // Format Rupiah dibulatkan total tanpa angka sen desimal di belakang koma
            document.getElementById('total-nominal-id').innerText = "Rp " + totalNominal.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            document.getElementById('total-penarikan-id').innerText = "Rp " + totalPenarikan.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            document.getElementById('total-saldo-id').innerText = "Rp " + totalSaldo.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }
    });
</script>
@endsection