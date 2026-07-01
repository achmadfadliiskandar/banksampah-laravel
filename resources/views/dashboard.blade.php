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
                                    <h3 class="mb-0 fw-bold" id="cardNasabah">
                                        {{ number_format($totalNasabah, 0, ',', '.') }}</h3>
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
                                    <h3 class="mb-0 fw-bold"><span
                                            id="cardSampah">{{ number_format($totalSampah, 1, ',', '.') }}</span> <small
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
                                    <h3 class="mb-0 fw-bold">Rp <span
                                            id="cardTabungan">{{ number_format($totalTabungan, 0, ',', '.') }}</span></h3>
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-white rounded">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate"
                            class="form-control form-control-sm shadow-sm"
                            value="{{ request('start_date', date('Y-m-d', strtotime('-7 days'))) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate" class="form-control form-control-sm shadow-sm"
                            value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 d-grid gap-2 d-md-flex">
                        <a href="#"
                            onclick="this.href='{{ route('dashboard.cetakPdf') }}?start_date=' + document.getElementById('startDate').value + '&end_date=' + document.getElementById('endDate').value"
                            target="_blank" class="btn btn-danger btn-sm shadow-sm w-50">
                            <i class="fas fa-file-pdf me-2"></i>Filter PDF
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm px-3 border shadow-sm w-50"><i
                                class="fas fa-undo me-1"></i>Reset</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-success me-2"></i>Grafik Periode Setoran
                            (Kg)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="setoranChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
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
                            {{-- <a href="{{ route('download-laporan') }}" class="btn btn-outline-secondary text-start p-3 shadow-sm w-100">
                                <div>
                                    <strong class="d-block">Download Laporan Bulanan</strong>
                                    <small class="text-muted">Ekspor rekapitulasi data setoran aktual (.pdf)</small>
                                </div>
                            </a> --}}
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
                    <h5 class="modal-title fw-bold" id="modalPieChartLabel"><i class="fas fa-chart-pie me-2"></i>
                        Proporsi Jenis Sampah</h5>
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
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm w-100"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CHARTS 1: BAR CHART
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
                plugins: [ChartDataLabels],
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '10%'
                        }
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#198754',
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: value => value > 0 ? value.toString().replace('.', ',') + ' Kg' : ''
                        }
                    }
                }
            });

            // CHARTS 2: PIE CHART
            const ctxPie = document.getElementById('sampahPieChart').getContext('2d');
            const pieLabels = {!! json_encode($pieLabels) !!};
            const dataPersen = {!! json_encode($pieDataPersen) !!};
            const dataBeratAsli = {!! json_encode($pieDataBerat) !!};

            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: dataPersen,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                            '#858796', '#6f42c1'
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
                        datalabels: {
                            color: '#ffffff',
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            textAlign: 'center',
                            formatter: function(value, context) {
                                const indexItem = context.dataIndex;
                                const nilaiKg = dataBeratAsli[indexItem];
                                if (nilaiKg === 0) return '';
                                return [nilaiKg + ' Kg', value + '%'];
                            },
                            anchor: 'center',
                            align: 'center'
                        }
                    }
                }
            });
        });
    </script>
@endsection
