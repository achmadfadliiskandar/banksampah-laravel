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

        <input type="file" id="camera-input" accept="image/*" hidden>

        <button class="btn btn-success rounded-pill w-100 py-3 fw-bold shadow-sm"
            onclick="document.getElementById('camera-input').click()">
            <i class="bi bi-camera-fill me-2"></i>AMBIL FOTO SAMPAH
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
        // Array global untuk menampung keranjang sesi setoran massal
        let keranjang = [];
        let temporaryImageBase64 = ""; // String cadangan foto lokal jika server tidak mengirim balik foto

        // --- 1. PROSES AMBIL GAMBAR DARI KAMERA / UPLOAD GALERI & PROSES AI ---
        document.getElementById('camera-input').onchange = function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // VALIDASI UKURAN FOTO LOKAL AWAL (Maksimal dinaikkan ke 10MB agar tidak tertolak di sisi client)
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    title: 'Foto Terlalu Besar!',
                    text: 'Ukuran foto maksimal adalah 10MB. Silakan ambil foto ulang.',
                    icon: 'warning'
                });
                this.value = '';
                return;
            }

            // Membaca file gambar mentah untuk preview lokal sementara (HTML5)
            const reader = new FileReader();
            reader.onload = function(event) {
                temporaryImageBase64 = event.target.result;
            };
            reader.readAsDataURL(file);

            // Tampilkan Loading Animasi SweetAlert2 secara instan
            Swal.fire({
                title: 'AI Sedang Berpikir...',
                html: 'Menganalisa jenis komponen sampah visual',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            // Siapkan Form Data untuk dikirim ke Controller Laravel
            let formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');

            // TEMBAK KE CONTROLLER LARAVEL PWA
            fetch("{{ route('pwa.scan-ai') }}", {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    // Ambil JSON mentah, jika gagal parsing set sebagai objek kosong
                    const data = await response.json().catch(() => ({}));
                    
                    // JIKA HTTP STATUS BUKAN 200 (Misal: 422 Validasi Gagal, 500 Server Drop)
                    if (!response.ok) {
                        let pesanEror = data.error || 
                                        (data.errors ? Object.values(data.errors).flat().join(', ') : '') || 
                                        'Gagal memproses gambar (Error ' + response.status + ')';
                        throw new Error(pesanEror);
                    }
                    return data;
                })
                .then(data => {
                    // Tutup loading secara bersih setelah mendapatkan response berkode 200
                    Swal.close(); 
                    
                    // KONDISI A: AI BERHASIL MENGENALI OBJEK & ADA DI DATABASE MASTER
                    if (data.status === 'success' && data.id) {
                        
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
                            confirmButtonText: 'âž• Masukkan ke List',
                            confirmButtonColor: '#198754',
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                // ðŸ”¥ STRATEGI PENTING: Gunakan data.foto_kompres dari Laravel agar string JSON berukuran sangat ringan
                                const fotoFinal = data.foto_kompres || temporaryImageBase64 || "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 24 24'><rect width='24' height='24' fill='%23eee'/></svg>";
                                
                                addToCart(data.id, data.nama, result.value, data.harga, fotoFinal);

                                // Penawaran Berkelanjutan (Looping Kamera/Galeri otomatis)
                                Swal.fire({
                                    title: 'Berhasil Masuk List!',
                                    text: 'Apakah kamu ingin memfoto jenis sampah yang lain untuk sesi ini?',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#198754',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'ðŸ“¸ Ya, Foto Lagi',
                                    cancelButtonText: 'Cukup, Lihat Nota'
                                }).then((pilihan) => {
                                    if (pilihan.isConfirmed) {
                                        document.getElementById('camera-input').click();
                                    }
                                });
                            }
                        });

                    // KONDISI B: AI MENOLAK OBJEK SECARA HALUS (status rejected / akurasi < 20%)
                    } else if (data.status === 'rejected' || !data.id) {
                        Swal.fire({
                            title: 'Objek Tidak Dikenali!',
                            text: data.label ? `Hasil analisa condong ke '${data.label}', namun akurasinya terlalu rendah.` : 'Kategori sampah belum terdaftar atau objek kurang jelas. Coba atur pencahayaan.',
                            icon: 'warning',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Coba Lagi'
                        });
                    }
                })
                .catch(err => {
                    // JIKA TERJADI EROR FATAL JARINGAN ATAU REJECTED BY SERVER VALIDATION
                    Swal.close(); // Hentikan animasi loading secara paksa agar tidak stuck berputar
                    console.error("Detail Log Eror Catch:", err);
                    
                    // Tampilkan pesan gangguan sistem lewat SweetAlert interaktif
                    Swal.fire({
                        title: 'Analisa Terhenti',
                        text: err.message || 'Terjadi gangguan jaringan saat berkomunikasi dengan server AI.',
                        icon: 'error',
                        confirmButtonText: 'Oke'
                    });
                });

            // Reset nilai input file agar event .onchange bisa dipicu kembali untuk foto baru berikutnya
            this.value = '';
        };

        // --- 2. MANAGEMENT STACK ARRAY KERANJANG GABUNGAN ---
        function addToCart(id, nama, berat, harga, fotoBase64) {
            keranjang.push({
                kategori_id: id,
                nama: nama,
                berat: parseFloat(berat),
                harga: parseInt(harga) || 0,
                foto: fotoBase64
            });
            renderCart();
        }

        function renderCart() {
            const containerBlock = document.getElementById('cart-container');
            const itemsContainer = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-msg');
            const gallery = document.getElementById('gallery-preview');
            const placeholderUi = document.getElementById('placeholder-ui');

            if(emptyMsg) emptyMsg.classList.add('d-none');
            if(containerBlock) containerBlock.classList.remove('d-none');
            if(placeholderUi) placeholderUi.classList.add('d-none');

            itemsContainer.innerHTML = '';
            gallery.innerHTML = ''; 
            let total = 0;

            keranjang.forEach((item, index) => {
                let subtotal = item.berat * item.harga;
                total += subtotal;

                gallery.innerHTML += `
                    <div class="position-relative" style="width: 65px; height: 65px;">
                        <img src="${item.foto}" class="rounded-3 border shadow-sm" style="width: 100%; height: 100%; object-fit: cover;">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.65rem;">
                            ${index + 1}
                        </span>
                    </div>
                `;

                itemsContainer.innerHTML += `
                <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded-3 shadow-sm border-start border-success border-3">
                    <div class="d-flex align-items-center gap-2">
                        <img src="${item.foto}" class="rounded-2 border" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <strong class="small d-block text-dark text-uppercase" style="font-size:0.8rem;">${index + 1}. ${item.nama}</strong>
                            <small class="text-muted" style="font-size:0.75rem;">${item.berat} Kg Ã— Rp ${item.harga.toLocaleString('id-ID')}</small>
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

            const totalUi = document.getElementById('total-estimasi');
            if(totalUi) totalUi.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        function removeItem(index) {
            keranjang.splice(index, 1);
            if (keranjang.length === 0) {
                if(document.getElementById('cart-container')) document.getElementById('cart-container').classList.add('d-none');
                if(document.getElementById('empty-msg')) document.getElementById('empty-msg').classList.remove('d-none');
                if(document.getElementById('gallery-preview')) document.getElementById('gallery-preview').innerHTML = '';
                if(document.getElementById('placeholder-ui')) document.getElementById('placeholder-ui').classList.remove('d-none');
            } else {
                renderCart();
            }
        }

        // --- 3. KIRIM SATU PAKET DATA MASSAL (SIMPAN SESI SETORAN) ---
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
                        html: 'Sedang menyimpan data transaksi ke server...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    // PROSES PENGIRIMAN DATA MASSAL DENGAN MENGGUNAKAN STRING BASE64 RINGAN (HASIL GD COMPRESS)
                    fetch("{{ route('pwa.setor') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                items: keranjang
                            })
                        })
                        .then(async res => {
                            const textData = await res.text();
                            try {
                                return JSON.parse(textData);
                            } catch(e) {
                                throw new Error(textData.substring(0, 200)); // Potong pesan error jika yang keluar halaman html crash
                            }
                        })
                        .then(res => {
                            Swal.close();
                            if (res.success) {
                                Swal.fire('Berhasil!', 'Satu sesi setoran sukses dicatat.', 'success')
                                    .then(() => window.location.href = "{{ route('pwa.riwayat') }}");
                            } else {
                                Swal.fire('Gagal Menyimpan', res.message || 'Terjadi gangguan internal basis data.', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.close();
                            console.error("Detail Eror Submit Massal:", err);
                            Swal.fire({
                                title: 'Gagal Menyimpan Sesi',
                                text: 'Pesan sistem: ' + err.message,
                                icon: 'error'
                            });
                        });
                }
            });
        };
    </script>
@endsection
