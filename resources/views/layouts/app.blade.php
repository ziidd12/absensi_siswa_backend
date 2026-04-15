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
        :root { 
            --primary-blue: #0047ff; 
            --bg-light: #f8f9fa; 
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            overflow-x: hidden; 
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: var(--primary-blue);
            color: white;
            padding: 20px 0 0 0;
            transition: all 0.3s;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-brand { 
            padding: 0 30px 20px 30px; 
            font-size: 22px; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        
        /* Menu yang bisa di-scroll */
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 10px;
        }
        
        /* Logout button container */
        .sidebar-logout {
            position: sticky;
            bottom: 0;
            background: var(--primary-blue);
            padding: 15px 0 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
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
        
        .nav-item-custom i { 
            font-size: 20px; 
            margin-right: 15px; 
            width: 24px;
            text-align: center;
        }
        
        .nav-item-custom:hover, .nav-item-custom.active {
            color: var(--primary-blue);
            background: white;
            border-radius: 30px 0 0 30px;
            margin-left: 20px;
        }

        /* Curve Effect untuk active menu */
        .nav-item-custom.active::before {
            content: ""; 
            position: absolute; 
            background: transparent; 
            height: 50px; 
            width: 50px;
            top: -50px; 
            right: 0; 
            border-radius: 50%; 
            box-shadow: 25px 25px 0 0 white;
        }
        
        .nav-item-custom.active::after {
            content: ""; 
            position: absolute; 
            background: transparent; 
            height: 50px; 
            width: 50px;
            bottom: -50px; 
            right: 0; 
            border-radius: 50%; 
            box-shadow: 25px -25px 0 0 white;
        }

        /* Scrollbar Styling */
        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Untuk Firefox */
        .sidebar-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.1);
        }

        /* Text muted untuk header menu */
        .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Main Content */
        .main-content { 
            margin-left: 260px; 
            padding: 25px; 
            min-height: 100vh; 
        }
        
        /* Navbar Custom */
        .top-nav {
            background: white;
            border-radius: 20px;
            padding: 12px 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .card-table { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">GoAbsen</div>
        
        <!-- Menu yang bisa di-scroll -->
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-item-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>

            <!-- Menu Master Data -->
            <div class="px-3 mt-3 mb-2">
                <small class="text-white-50 text-uppercase fw-bold">Master Data</small>
            </div>
            
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
<!-- 
            <a href="{{ route('anggota-kelas.index') }}" class="nav-item-custom {{ request()->routeIs('anggota-kelas.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Data Anggota Kelas
            </a>
             -->
            <a href="{{ route('tahun-ajaran.index') }}" class="nav-item-custom {{ request()->routeIs('tahun-ajaran.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Tahun Ajaran
            </a>

            <a href="{{ route('mapel.index') }}" class="nav-item-custom {{ request()->routeIs('mapel.*') ? 'active' : '' }}">
                <i class="bi bi-book-fill"></i> Data Mapel
            </a>

            <a href="{{ route('jadwal.index') }}" class="nav-item-custom {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Data Jadwal
            </a>

            <a href="{{ route('leaderboardAdmin.index') }}" class="nav-item-custom {{ request()->routeIs('leaderboardAdmin.*') ? 'active' : '' }}">
                <i class="bi bi-trophy"></i> <span>Ranking Poin</span>
            </a>

            <!-- Menu Penilaian -->
            <div class="px-3 mt-3 mb-2">
                <small class="text-white-50 text-uppercase fw-bold">Penilaian</small>
            </div>

            <a href="{{ route('setup-penilaian.kategori.index') }}" class="nav-item-custom {{ request()->routeIs('setup-penilaian.kategori.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check-fill"></i> Kategori Penilaian
            </a>
<!-- 
            <a href="{{ route('setup-penilaian.pertanyaan.index') }}" class="nav-item-custom {{ request()->routeIs('setup-penilaian.pertanyaan.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check-fill"></i> Pernyataan Kategori
            </a> -->

            <!-- Menu Laporan -->
            <div class="px-3 mt-3 mb-2">
                <small class="text-white-50 text-uppercase fw-bold">Laporan</small>
            </div>

            <a href="{{ route('monitoring-nilai.index') }}" class="nav-item-custom {{ request()->routeIs('monitoring-nilai.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Laporan Penilaian
            </a>

            <!-- Menu Akun -->
            <div class="px-3 mt-3 mb-2">
                <small class="text-white-50 text-uppercase fw-bold">Akun</small>
            </div>

            <a href="{{ route('profile.edit') }}" class="nav-item-custom {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a>
        </nav>

        <!-- Logout Button (Fixed di bawah) -->
        <div class="sidebar-logout">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item-custom bg-transparent border-0 w-100 text-start">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
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
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=0047ff&color=fff&size=40" class="rounded-circle border border-2 border-primary" width="40" height="40">
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

        <!-- Content Slot -->
        <div class="container-fluid p-0">
    @if(isset($slot))
        {{ $slot }}
    @else
        @yield('content')
    @endif
</div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Optional: Stack for custom scripts -->
    @stack('scripts')
</body>
</html>