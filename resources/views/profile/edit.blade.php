<x-app-layout>
    @section('title', 'Profil Pengguna')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4" style="border-radius: 20px;">
                    <div class="card-body">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0d6efd&color=fff&size=128" 
                             class="rounded-circle shadow-sm mb-3" 
                             style="width: 120px; border: 4px solid #f8f9fa;">
                        
                        <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                        <p class="text-muted small mb-3">{{ strtoupper($user->role) }}</p>
                        
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                            Akun Aktif
                        </span>
                        
                        <hr class="my-4" style="opacity: 0.1;">
                        
                        <div class="text-start">
                            <small class="text-muted d-block italic">Bergabung sejak:</small>
                            <p class="fw-medium">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary text-white rounded-3 p-2 me-3">
                            <i class="bi bi-person-vcard fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Informasi Pribadi</h5>
                    </div>

                    <form>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                                <input type="text" class="form-control form-control-lg bg-light border-0" 
                                       value="{{ $user->name }}" readonly style="border-radius: 12px;">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Alamat Email</label>
                                <input type="email" class="form-control form-control-lg bg-light border-0" 
                                       value="{{ $user->email }}" readonly style="border-radius: 12px;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Hak Akses / Role</label>
                                <input type="text" class="form-control form-control-lg bg-light border-0" 
                                       value="{{ ucfirst($user->role) }}" readonly style="border-radius: 12px;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Device ID (Mobile)</label>
                                <input type="text" class="form-control form-control-lg bg-light border-0 text-truncate" 
                                       value="{{ $user->device_id ?? 'Belum Terhubung' }}" readonly style="border-radius: 12px;">
                            </div>
                        </div>

                        <div class="mt-5 p-3 bg-yellow-50 rounded-3 border border-warning-subtle" style="background-color: #fff9e6;">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-4"></i>
                                <div>
                                    <h6 class="fw-bold text-warning-emphasis mb-1">Mode Lihat Saja</h6>
                                    <p class="small text-muted mb-0">Halaman ini hanya untuk menampilkan informasi akun. Untuk perubahan data, silakan hubungi Administrator sistem.</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>