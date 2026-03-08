<x-guest-layout>
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">Selamat Datang</h3>
        <p class="text-muted small">Silakan login untuk mengakses sistem absensi</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-secondary">Alamat Email</label>
            <input id="email" type="email" name="email" 
                class="form-control @error('email') is-invalid @enderror" 
                value="{{ old('email') }}" required autofocus placeholder="name@example.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold text-secondary">Password</label>
            <input id="password" type="password" name="password" 
                class="form-control @error('password') is-invalid @enderror" 
                required placeholder="Masukkan password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label small text-secondary" for="remember_me">
                    Ingat saya
                </label>
            </div>
            @if (Route::has('password.request'))
                <a class="text-decoration-none small" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary shadow-sm">
                MASUK SEKARANG
            </button>
        </div>

        <div class="text-center mt-4">
            <p class="small text-muted">Belum punya akun? <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Daftar</a></p>
        </div>
    </form>
</x-guest-layout>