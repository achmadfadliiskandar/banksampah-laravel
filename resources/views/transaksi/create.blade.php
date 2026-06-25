@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="font-weight-bold text-success mb-4">Input Setoran Sampah</h5>

                <form action="{{ route('transaksi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="text-center mb-3">
                        <div id="camera-area" class="bg-light border rounded d-flex align-items-center justify-content-center"
                            style="height: 200px; overflow: hidden;">
                            <img id="preview" src="" class="img-fluid d-none">
                            <i id="placeholder-icon" class="fas fa-camera fa-3x text-secondary"></i>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2"
                            onclick="document.getElementById('input-foto').click()">
                            <i class="fas fa-camera mr-1"></i> Ambil Foto / Scan AI
                        </button>
                        <input type="file" name="foto" id="input-foto" accept="image/*" capture="environment"
                            class="d-none" onchange="previewAndScan(this)">
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Estimasi Berat (Kg)</label>
                        <input type="number" step="0.01" name="berat" class="form-control" placeholder="0.00"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Kategori Terdeteksi AI</label>
                        <select name="kategori" id="kategori-ai" class="form-control bg-light" required>
                            <option value="">Menunggu scan...</option>
                            <option value="plastic">PLASTIC</option>
                            <option value="paper">PAPER</option>
                            <option value="glass">GLASS</option>
                            <option value="metal">METAL</option>
                            <option value="cardboard">CARDBOARD</option>
                            <option value="trash">LAINNYA (TRASH)</option>
                        </select>
                        <small class="text-muted" id="ai-status"></small>
                    </div>

                    <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold mt-4">Simpan
                        Setoran</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewAndScan(input) {
            if (input.files && input.files[0]) {
                // 1. Tampilkan Preview Foto
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('preview').classList.remove('d-none');
                    document.getElementById('placeholder-icon').classList.add('d-none');
                }
                reader.readAsDataURL(input.files[0]);

                // 2. Kirim ke Server Flask AI via Ngrok
                let formData = new FormData();
                formData.append('file', input.files[0]);

                document.getElementById('ai-status').innerText = "Sedang menganalisis...";

                // GANTI URL INI DENGAN URL FLASK KAMU (bisa localhost:5000 jika satu laptop)
                fetch('http://127.0.0.1:5005/api/predict', {
                    method: 'POST',
                    body: formData
                })
                // fetch('https://341e-2001-448a-2034-23b1-e0ae-fd63-704b-6c34.ngrok-free.app/api/predict', {
                //         method: 'POST',
                //         body: formData
                //     })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Data dari Flask:", data);

                        // Kita cek apakah ada data kategori (baik status success atau warning)
                        if (data.kategori) {
                            const hasil = data.kategori.toLowerCase().trim();
                            const dropdown = document.getElementById('kategori-ai');
                            const statusEl = document.getElementById('ai-status');

                            // 1. Pilih Dropdown otomatis
                            dropdown.value = hasil;

                            // 2. Tampilkan Status Berdasarkan Kondisi (Success/Warning)
                            if (data.status === 'success') {
                                statusEl.innerText = "Terdeteksi: " + data.kategori.toUpperCase() + " (" + data
                                    .akurasi + "%)";
                                statusEl.className = "text-success small font-weight-bold";
                            } else if (data.status === 'warning') {
                                statusEl.innerText = "AI Ragu (" + data.akurasi + "%), Mungkin: " + data.kategori
                                    .toUpperCase();
                                statusEl.className = "text-warning small font-weight-bold";
                            }

                            console.log("✅ UI Updated!");
                        } else {
                            // Jika status error dari Flask
                            document.getElementById('ai-status').innerText = "Gagal: " + (data.message ||
                            "Data kosong");
                            document.getElementById('ai-status').className = "text-danger small";
                        }
                    })
                    .catch(error => {
                        console.error("Fetch Error:", error);
                        document.getElementById('ai-status').innerText = "Koneksi terputus atau Server AI mati.";
                        document.getElementById('ai-status').className = "text-danger small";
                    });
            }
        }
    </script>
@endsection
