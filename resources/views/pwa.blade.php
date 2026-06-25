<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#198754">
    <title>Bank Sampah - PWA Nasabah</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f7f6; padding-bottom: 80px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .pwa-container { max-width: 500px; margin: 0 auto; }
        .card { border: none; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .bottom-nav { 
            position: fixed; bottom: 0; width: 100%; max-width: 500px; 
            background: white; border-top: 1px solid #eee; display: flex; 
            justify-content: space-around; padding: 10px 0; z-index: 1000;
        }
        .nav-item { text-align: center; color: #6c757d; text-decoration: none; font-size: 0.75rem; }
        .nav-item.active { color: #198754; font-weight: bold; }
        .scan-btn-container { margin-top: -35px; }
        .scan-btn { 
            width: 60px; height: 60px; background: #198754; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            color: white; font-size: 1.5rem; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.4);
        }
        /* Proteksi CSS untuk Laptop */
        @media (min-width: 992px) {
            body { display: none; }
            html::after { content: "Maaf, akses hanya melalui Smartphone."; display: flex; justify-content: center; align-items: center; height: 100vh; font-weight: bold; }
        }
    </style>
</head>
<body>
    <div class="pwa-container">
        <div class="container pt-4">
            @yield('content')
        </div>

        <div class="bottom-nav">
            <a href="{{ route('pwa.home') }}" class="nav-item {{ Request::is('pwa/home') ? 'active' : '' }}">
                <i class="bi bi-grid-fill fs-4"></i><br>Beranda
            </a>
            <form id="logout-form-pwa" action="{{ route('pwa.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">keluar</button>
            </form>
            <div class="scan-btn-container">
                <a href="{{ route('pwa.scan') }}" class="scan-btn">
                    <i class="bi bi-camera-fill"></i>
                </a>
            </div>
            <a href="{{ route('pwa.riwayat') }}" class="nav-item {{ Request::is('pwa/riwayat') ? 'active' : '' }}">
                <i class="bi bi-clock-history fs-4"></i><br>Riwayat
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>