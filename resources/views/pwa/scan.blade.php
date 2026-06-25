@extends('layouts.pwa')

@section('content')
    <div class="mb-4 text-center">
        <h4 class="fw-bold text-success">Scan Sampah AI</h4>
        <p class="text-muted small">Foto sampahmu dan biarkan AI menghitungnya</p>
    </div>

    <div class="card mb-4 p-3 text-center border-0 shadow-sm rounded-4">
        <div id="preview-container" class="mb-3">
            <div id="placeholder-ui" class="bg-light rounded-4 d-flex flex-column align-items-center justify-content-center"
                style="height: 180px; border: 2px dashed #198754;">
                <i class="bi bi-camera-fill text-success mb-2" style="font-size: 3rem;"></i>
                <span class="text-muted small">Klik tombol di bawah untuk memfoto</span>
            </div>

            <div id="gallery-preview" class="d-flex flex-wrap gap-2 justify-content-center"></div>
        </div>

        <input type="file" id="camera-input" accept="image/*" capture="environment" hidden>

        <button class="btn btn-success rounded-pill w-100 py-3 fw-bold shadow-sm"
            onclick="document.getElementById('camera-input').click()">
            <i class="bi bi-camera-fill me-2"></i>📸 AMBIL FOTO SAMPAH
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-seam-fill me-2 text-success"></i>Daftar Scan Sesi Ini
            </h6>
        </div>

        <div class="card-body bg-white pt-0">
            <div id="cart-container" class="p-3 rounded-4 bg-light d-none border">
                <div id="cart-items" class="d-flex flex-column gap-2">
                </div>

                <div id="summary-section" class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small fw-bold">Total Estimasi Tabungan:</span>
                        <span class="fs-4 fw-bold text-success" id="total-estimasi">Rp 0</span>
                    </div>
                    <button class="btn btn-success w-100 rounded-pill py-3 fw-bold shadow-sm" id="btn-submit">
                        SIMPAN KE DATABASE <i class="bi bi-cloud-arrow-up-fill ms-2"></i>
                    </button>
                </div>
            </div>

            <div class="text-center py-5" id="empty-msg">
                <i class="bi bi-cart-x text-muted opacity-25" style="font-size: 3.5rem;"></i>
                <p class="text-muted small mt-2 mb-0">Belum ada sampah yang berhasil discan dalam sesi ini</p>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Array global untuk mengumpulkan banyak data dan banyak foto dalam satu sesi
        let keranjang = [];

        // --- 1. PROSES AMBIL GAMBAR DARI KAMERA & PROSES TRANSLASI AI ---
        document.getElementById('camera-input').onchange = function(e) {
            const file = e.target.files[0];
            if (!file) return;
            // VALIDASI: Jika ukuran 1 file foto di atas 4MB, batalkan dan beri peringatan
            if (file.size > 4 * 1024 * 1024) {
                Swal.fire({
                    title: 'Foto Terlalu Besar!',
                    text: 'Ukuran foto maksimal adalah 4MB. Silakan turunkan resolusi kamera HP Anda atau ambil foto ulang.',
                    icon: 'warning'
                });
                this.value = '';
                return;
            }

            // Membaca file gambar mentah untuk diubah menjadi string Base64 sebagai thumbnail unik
            const reader = new FileReader();
            let temporaryImageBase64 = "";

            reader.onload = function(event) {
                temporaryImageBase64 = event.target.result;
            }
            reader.readAsDataURL(file);

            // Munculkan Loading Animasi SweetAlert2
            Swal.fire({
                title: 'AI Sedang Berpikir...',
                html: 'Menganalisa jenis komponen sampah visual',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // Siapkan Form Data untuk dikirim ke Controller (Proses Scan Tunggal)
            let formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Tembak Rute Scan AI Laravel
            fetch("{{ route('pwa.scan-ai') }}", {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.error || 'Terjadi kesalahan sistem (Error ' + response.status +
                            ')');
                    }
                    return data;
                })
                .then(data => {
                    Swal.close();

                    if (data.id) {
                        // Munculkan Pop-up Input Berat Khusus untuk Barang yang Baru Saja Discan
                        Swal.fire({
                            title: 'Terdeteksi: ' + data.nama,
                            text: `Akurasi AI: ${data.akurasi}%. Masukkan perkiraan beratnya (Kg):`,
                            input: 'number',
                            inputAttributes: {
                                step: '0.1',
                                min: '0.1',
                                autofocus: 'true'
                            },
                            inputValue: 1,
                            confirmButtonText: '➕ Masukkan ke List',
                            confirmButtonColor: '#198754',
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                // MASUKKAN KE KERANJANG (Mengikat ID, Data, dan string Foto uniknya)
                                addToCart(data.id, data.nama, result.value, data.harga,
                                    temporaryImageBase64);

                                // Penawaran Berkelanjutan (Looping Interaksi Kamera)
                                Swal.fire({
                                    title: 'Berhasil Masuk List!',
                                    text: 'Apakah kamu ingin memfoto jenis sampah yang lain untuk sesi ini?',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#198754',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: '📸 Ya, Foto Lagi',
                                    cancelButtonText: 'Cukup, Lihat Nota'
                                }).then((pilihan) => {
                                    if (pilihan.isConfirmed) {
                                        // Otomatis klik tombol jepret kamera kembali tanpa klik manual di web
                                        document.getElementById('camera-input').click();
                                    }
                                });
                            }
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    console.error(err);
                    Swal.fire({
                        title: 'Informasi Scan',
                        text: err.message,
                        icon: 'warning'
                    });
                });

            // Reset nilai input file agar fungsi onchange bisa terus dipicu objek yang sama secara berturut-turut
            this.value = '';
        };

        // --- 2. MANAGEMENT STACK ARRAY KERANJANG GABUNGAN ---
        function addToCart(id, nama, berat, harga, fotoBase64) {
            // SINKRONISASI: Properti diisi dengan nama 'kategori_id' agar dibaca utuh oleh $item['kategori_id'] di Laravel
            keranjang.push({
                kategori_id: id,
                nama: nama,
                berat: parseFloat(berat),
                harga: parseInt(harga) || 0,
                foto: fotoBase64
            });
            renderCart();
        }

        // Fungsi menggambar barisan item ke dalam satu kotak kelompok abu-abu induk
        function renderCart() {
            const containerBlock = document.getElementById('cart-container');
            const itemsContainer = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-msg');
            const gallery = document.getElementById('gallery-preview');
            const placeholderUi = document.getElementById('placeholder-ui');

            emptyMsg.classList.add('d-none');
            containerBlock.classList.remove('d-none');
            placeholderUi.classList.add('d-none');

            itemsContainer.innerHTML = '';
            gallery.innerHTML = ''; // Kosongkan galeri atas untuk digambar ulang secara massal
            let total = 0;

            keranjang.forEach((item, index) => {
                let subtotal = item.berat * item.harga;
                total += subtotal;

                // A. Render Ulang Deretan Thumbnail Foto di bagian atas secara horizontal (Semuanya akan muncul!)
                gallery.innerHTML += `
                    <div class="position-relative" style="width: 65px; height: 65px;">
                        <img src="${item.foto}" class="rounded-3 border shadow-sm" style="width: 100%; height: 100%; object-fit: cover;">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.65rem;">
                            ${index + 1}
                        </span>
                    </div>
                `;

                // B. Render Ulang Baris Rincian Item dengan Urutan Nomor (1, 2, 3, dst) mengalir ke bawah
                itemsContainer.innerHTML += `
                <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded-3 shadow-sm border-start border-success border-3">
                    <div class="d-flex align-items-center gap-2">
                        <img src="${item.foto}" class="rounded-2 border" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <strong class="small d-block text-dark text-uppercase" style="font-size:0.8rem;">${index + 1}. ${item.nama}</strong>
                            <small class="text-muted" style="font-size:0.75rem;">${item.berat} Kg × Rp ${item.harga.toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success small mb-0">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        <button class="btn btn-sm text-danger border-0 p-0" onclick="removeItem(${index})">
                            <i class="bi bi-trash3" style="font-size:0.9rem;"></i>
                        </button>
                    </div>
                </div>
                `;
            });

            document.getElementById('total-estimasi').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Fungsi menghapus item tertentu jika salah foto atau salah input berat
        function removeItem(index) {
            keranjang.splice(index, 1);
            if (keranjang.length === 0) {
                // Kembalikan ke tampilan default kosong jika semua isi list dihapus
                document.getElementById('cart-container').classList.add('d-none');
                document.getElementById('empty-msg').classList.remove('d-none');
                document.getElementById('gallery-preview').innerHTML = '';
                document.getElementById('placeholder-ui').classList.remove('d-none');
            } else {
                renderCart();
            }
        }

        // --- 3. KIRIM SATU PAKET DATA MASSAL (PAYLOAD JSON) KE CONTROLLER LARAVEL ---
        document.getElementById('btn-submit').onclick = function() {
            if (keranjang.length === 0) return;

            Swal.fire({
                title: 'Simpan Sesi Transaksi?',
                text: `Seluruh data (${keranjang.length} jenis sampah) akan langsung dibungkus menjadi 1 nomor nota transaksi resmi.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan Sesi!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengunci data...',
                        html: 'Sedang mengonversi dan menyimpan data fisik foto ke peladen(server)',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    // Tembak URL Rute simpanSetoran di Controllermu (Sesuaikan dengan rute POST web.php kamu)
                    fetch("{{ route('pwa.setor') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json', // <-- Tambahkan ini agar Laravel tahu ini request API/JSON
                                // 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            },
                            // Mengirimkan seluruh isi array 'keranjang' dalam bentuk JSON string tunggal
                            body: JSON.stringify({
                                items: keranjang
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            Swal.close();
                            if (res.success) {
                                Swal.fire('Berhasil!',
                                        'Satu sesi setoran kelompok sukses dicatat ke database.', 'success')
                                    .then(() => window.location.href = "{{ route('pwa.riwayat') }}");
                            } else {
                                // Menangkap pesan error asli bawaan tangkapan try-catch Laravel Controller
                                Swal.fire('Gagal Menyimpan', res.message ||
                                    'Terjadi gangguan internal sistem.', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.close();
                            console.error(err);
                            Swal.fire('Error',
                                'Terjadi gangguan koneksi jaringan saat mengirim data masal.', 'error');
                        });
                }
            });
        };
    </script>
@endsection
