<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Absensi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root { --primary-blue: #0047ff; --bg-light: #f8f9fa; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: var(--primary-blue);
            color: white;
            padding: 20px 0;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-brand { padding: 20px 30px; font-size: 22px; font-weight: 700; margin-bottom: 30px; }
        .nav-item-custom {
            padding: 12px 30px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.7);
            text-decoration: none !important;
            transition: 0.3s;
            margin: 5px 0;
            position: relative;
        }
        .nav-item-custom i { font-size: 20px; margin-right: 15px; }
        .nav-item-custom:hover, .nav-item-custom.active {
            color: var(--primary-blue);
            background: white;
            border-radius: 30px 0 0 30px;
            margin-left: 20px;
        }

        /* Curve Effect */
        .nav-item-custom.active::before {
            content: ""; position: absolute; background: transparent; height: 50px; width: 50px;
            top: -50px; right: 0; border-radius: 50%; box-shadow: 25px 25px 0 0 white;
        }
        .nav-item-custom.active::after {
            content: ""; position: absolute; background: transparent; height: 50px; width: 50px;
            bottom: -50px; right: 0; border-radius: 50%; box-shadow: 25px -25px 0 0 white;
        }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 25px; min-height: 100vh; }
        
        /* Navbar Custom */
        .top-nav {
            background: white;
            border-radius: 20px;
            padding: 12px 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .card-table { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        
        /* Logout Button Position */
        .sidebar nav {
            height: calc(100% - 100px);
            position: relative;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">AbsenSiswa</div>
        <nav>
            <a href="{{ route('dashboard') }}" class="nav-item-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-item-custom {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i> Manajemen User
            </a>
            <a href="{{ route('guru.index') }}" class="nav-item-custom {{ request()->routeIs('guru.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Data Guru
            </a>
            <a href="{{ route('siswa.index') }}" class="nav-item-custom {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Data Siswa
            </a>
            <a href="{{ route('kelas.index') }}" class="nav-item-custom {{ request()->routeIs('kelas.*') ? 'active' : '' }}">
                <i class="bi bi-door-open-fill"></i> Data Kelas
            </a>
            <a href="{{ route('tahun-ajaran.index') }}" class="nav-item-custom {{ request()->routeIs('tahun-ajaran.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Tahun Ajaran
            </a>
            <div style="position: absolute; bottom: 30px; width: 100%;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item-custom bg-transparent border-0 w-100 text-start">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-nav d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="fw-bold mb-0">@yield('title', 'Dashboard')</h5>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="me-3 text-end d-none d-md-block">
                    <small class="text-muted d-block" style="font-size: 11px;">Selamat Datang,</small>
                    <span class="fw-bold" style="font-size: 14px;">{{ Auth::user()->name }}</span>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=0047ff&color=fff" class="rounded-circle border border-2 border-primary" width="40">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3" style="border-radius: 15px; min-width: 200px;">
                        <li><h6 class="dropdown-header">Menu Profil</h6></li>
                        <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Akun Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-left me-2"></i> Keluar Aplikasi
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="container-fluid p-0">
            {{ $slot }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>