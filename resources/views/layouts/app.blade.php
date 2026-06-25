<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Bank Sampah">
    <link rel="apple-touch-icon" href="{{ asset('icons/bank-sampah.png') }}">
    <title>Admin Bank Sampah</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            overflow-x: hidden;
        }

        #sidebar {
            min-height: 100vh;
            width: 250px;
            background-color: #198754;
            transition: all 0.3s;
        }

        #sidebar .sidebar-heading {
            padding: 1.5rem 1.25rem;
            color: white;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #sidebar .list-group-item {
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            border: none;
            padding: 1rem 1.5rem;
        }

        #sidebar .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        #sidebar .list-group-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-left: 4px solid #ffc107;
        }

        #page-content-wrapper {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <div id="sidebar">
            <div class="sidebar-heading text-center"><i class="fas fa-recycle me-2"></i> ADMIN BS</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('dashboard') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.password.edit') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('admin.password.edit') ? 'active' : '' }}">
                    <i class="fas fa-cogs fa-sm fa-fw me-2"></i> Ganti Password
                </a>
                <a href="{{ route('kategori.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                    <i class="fas fa-tags me-2"></i> Master Kategori
                </a>
                <a href="{{ route('transaksi.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt me-2"></i> Input Setoran
                </a>
                <a href="{{ route('users.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i> Data Nasabah
                </a>
                <div class="mt-5 px-3">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 btn-sm shadow-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="page-content-wrapper" class="bg-light">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <span class="navbar-text">Halo, <strong>{{ Auth::user()->name }}</strong></span>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            // Tambahkan parameter unik agar tidak kena cache lama
            navigator.serviceWorker.register('/sw.js?v=' + Date.now(), {
                    scope: '/'
                })
                .then(function(registration) {
                    console.log('✅ Berhasil! Service Worker aktif.');
                })
                .catch(function(error) {
                    console.log('❌ Gagal lagi: ', error);
                });
        });
    }
</script>
</html>
