<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bank Sampah PWA</title>
    
    <!-- === PENGATURAN ICON & TAMPILAN STANDALONE UNTUK HP === -->
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=3">
    <meta name="theme-color" content="#198754">
    
    <!-- 1. Paksa Android Chrome untuk Full Screen / Standalone -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Bank Sampah">

    <!-- 2. Paksa iPhone/Safari (iOS) untuk Full Screen (MENGHILANGKAN ADDRESS BAR) -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Bank Sampah">

    <!-- 3. Link Icon 192x192 Khusus untuk di Layar Depan HP -->
    <link rel="icon" sizes="192x192" href="{{ asset('icons/bank-sampah.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/bank-sampah.png') }}">
    <!-- ======================================================= -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 70px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-around;
            z-index: 1000;
        }

        .nav-item {
            text-align: center;
            padding: 10px 0;
            color: #6c757d;
            text-decoration: none;
            flex: 1;
        }

        .nav-item.active {
            color: #198754;
            font-weight: bold;
        }

        .nav-item.text-danger {
            color: #dc3545 !important;
        }
    </style>
</head>

<body>

    <div class="container py-3">
        @yield('content')
    </div>

    <nav class="bottom-nav">
        <a href="{{ route('pwa.home') }}" class="nav-item {{ Request::is('pwa/home') ? 'active' : '' }}">
            <i class="bi bi-house-door fs-4"></i><br><small>Home</small>
        </a>
        <a href="{{ route('pwa.scan') }}" class="nav-item {{ Request::is('pwa/scan') ? 'active' : '' }}">
            <i class="bi bi-camera fs-4"></i><br><small>Scan</small>
        </a>
        <a href="{{ route('pwa.riwayat') }}" class="nav-item {{ Request::is('pwa/riwayat') ? 'active' : '' }}">
            <i class="bi bi-clock-history fs-4"></i><br><small>Riwayat</small>
        </a>

        <a href="javascript:void(0)" class="nav-item text-danger" onclick="confirmLogout()">
            <i class="bi bi-box-arrow-right fs-4"></i><br><small>Keluar</small>
        </a>

        {{-- Form Logout Tersembunyi --}}
        <form id="logout-form-pwa" action="{{ route('pwa.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar Aplikasi?',
                text: "Anda harus login kembali untuk mengakses app ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form-pwa').submit();
                }
            })
        }
    </script>

    <!-- === TAMBAHAN SCRIPT PWA (WAJIB) === -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('ServiceWorker PWA sukses aktif!');
                })
                .catch(error => {
                    console.log('ServiceWorker gagal:', error);
                });
            });
        }
    </script>
    <!-- =================================== -->
</body>

</html>