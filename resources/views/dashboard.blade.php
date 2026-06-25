@extends('layouts.app')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Ringkasan Statistik</h2>
            <span class="badge bg-success py-2 px-3">{{ date('d F Y') }}</span>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <a href="{{ route('dashboard.detail', 'nasabah') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Total Nasabah
                                    </h6>
                                    <h3 class="mb-0 fw-bold">{{ number_format($totalNasabah, 0, ',', '.') }}</h3>
                                </div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 mb-4">
                <a href="{{ route('dashboard.detail', 'sampah') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Sampah
                                        Terkumpul</h6>
                                    <h3 class="mb-0 fw-bold">{{ number_format($totalSampah, 1, ',', '.') }} <small
                                            style="font-size: 0.9rem;">Kg</small></h3>
                                </div>
                                <i class="fas fa-dumpster fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 mb-4">
                <a href="{{ route('dashboard.detail', 'tabungan') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Total Saldo
                                    </h6>
                                    <h3 class="mb-0 fw-bold">Rp {{ number_format($totalTabungan, 0, ',', '.') }}</h3>
                                </div>
                                <i class="fas fa-wallet fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm bg-info text-white h-100" data-bs-toggle="modal"
                    data-bs-target="#modalPieChart" style="cursor: pointer;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Populer (Klik
                                    Detail)</h6>
                                <h3 class="mb-0 fw-bold" style="font-size: 1.3rem;">{{ $kategoriPopuler }}</h3>
                            </div>
                            <i class="fas fa-chart-pie fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-success me-2"></i>Grafik Setoran 7 Hari
                            Terakhir (Kg)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="setoranChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('transaksi.index') }}"
                                class="btn btn-outline-success text-start p-3 shadow-sm">
                                <i class="fas fa-plus me-2"></i> Input Setoran Baru
                            </a>
                            <a href="{{ route('kategori.index') }}"
                                class="btn btn-outline-primary text-start p-3 shadow-sm">
                                <i class="fas fa-list me-2"></i> Kelola Kategori Harga
                            </a>
                            <a href="{{ route('download-laporan') }}" class="btn btn-outline-secondary text-start p-3 shadow-sm w-100">
                                {{-- <i class="fas fa-file-pdf text-danger me-2"></i>  --}}
                                <div>
                                    <strong class="d-block">Download Laporan Bulanan</strong>
                                    <small class="text-muted">Ekspor rekapitulasi data setoran aktual (.pdf)</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPieChart" tabindex="-1" aria-labelledby="modalPieChartLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold" id="modalPieChartLabel">
                        <i class="fas fa-chart-pie me-2"></i> Proporsi Jenis Sampah
                    </h5>
                    {{-- <button type="button" class="btn-close btn-close-white" data-bs-close="modal" aria-label="Close"></button> --}}
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted text-center small mb-4">
                        Visualisasi persentase riil dari total volume berat (Kg) setoran berdasarkan klasifikasi model
                        MobileNetV2.
                    </p>
                    <div style="max-width: 280px; margin: 0 auto;">
                        <canvas id="sampahPieChart"></canvas>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm w-100" data-bs-close="modal"
                        data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // -------------------------------------------------------------
        // CHARTS 1: BAR CHART (Setoran 7 Hari Terakhir) - DENGAN TEXT NILAI
        // -------------------------------------------------------------
        const ctxBar = document.getElementById('setoranChart').getContext('2d');
        const barLabels = {!! json_encode($chartLabels) !!};
        const barData = {!! json_encode($chartData) !!};

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'Total Berat Sampah (Kg)',
                    data: barData,
                    backgroundColor: 'rgba(25, 135, 84, 0.5)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            // 🔥 DAFTARKAN PLUGIN DI SINI
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        // Berikan ruang tambahan di atas grafik agar teks angka tidak terpotong
                        grace: '10%'
                    }
                },
                // 🔥 KONFIGURASI TEKS ANGKA DI ATAS BATANG
                plugins: {
                    datalabels: {
                        anchor: 'end', // Posisi jangkar di ujung atas batang
                        align: 'top', // Teks ditaruh tepat di atas batang
                        color: '#198754', // Warna teks (disesuaikan dengan tema sukses/hijau)
                        font: {
                            weight: 'bold', // Membuat teks angka menjadi tebal
                            size: 12 // Ukuran font teks
                        },
                        formatter: function(value) {
                            // Jika nilainya lebih dari 0, tampilkan angka + ' Kg', kalau 0 kosongi saja biar rapih
                            return value > 0 ? value.toString().replace('.', ',') + ' Kg' : '';
                        }
                    }
                }
            }
        });

        // -------------------------------------------------------------
        // CHARTS 2: PIE CHART MODAL (Tampil Nama Jenis + Persen Instan)
        // -------------------------------------------------------------
        const ctxPie = document.getElementById('sampahPieChart').getContext('2d');
        const pieLabels = {!! json_encode($pieLabels) !!};
        const dataPersen = {!! json_encode($pieDataPersen) !!};
        const dataBeratAsli = {!! json_encode($pieDataBerat) !!}; // 🔥 Tangkap array berat dari Laravel

        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: dataPersen, // Grafik lingkaran tetap diproporsikan berdasarkan persen
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796',
                        '#6f42c1'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    // 🔥 KONFIGURASI MULTI-LINE VALUE SEPERTI EXCEL
                    datalabels: {
                        color: '#ffffff', // Warna teks putih tebal agar kontras
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        textAlign: 'center', // Membuat baris teks rata tengah

                        // Fungsi kustomisasi menggabungkan Nilai Asli (Kg) dan Persen
                        formatter: function(value, context) {
                            const indexItem = context.dataIndex;
                            const nilaiKg = dataBeratAsli[
                            indexItem]; // Ambil data Kg yang bersesuaian

                            // Jika nilai 0, jangan tampilkan agar chart tidak penuh sesak
                            if (nilaiKg === 0) return '';

                            // 🔥 Trik Return Array = Otomatis Enter/Membuat baris baru di Chart.js
                            return [
                                nilaiKg + ' Kg', // Baris 1: Contoh "7 Kg"
                                value + '%' // Baris 2: Contoh "36.8%"
                            ];
                        },
                        anchor: 'center',
                        align: 'center'
                    }
                }
            }
        });
    });
</script>
