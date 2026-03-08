<x-app-layout>
    @section('title', 'Data Guru')

    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Daftar Tenaga Pengajar</h5>
                        <small class="text-muted">Kelola profil guru dan sinkronisasi akun akses</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Guru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>Email Akun</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $guru)
                            <tr>
                                <td class="fw-bold text-primary">{{ $guru->NIP }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($guru->nama_guru) }}&background=0047ff&color=fff" class="rounded-circle me-3" width="35">
                                        {{ $guru->nama_guru }}
                                    </div>
                                </td>
                                <td>{{ $guru->user->email ?? '-' }}</td>
                                <td><span class="badge rounded-pill bg-success-subtle text-success px-3 small">Aktif</span></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-light btn-sm rounded-3 shadow-sm border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit{{ $guru->id }}">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </button>
                                        
                                        <form action="{{ route('guru.destroy', $guru->id) }}" method="POST" onsubmit="return confirm('Hapus data guru ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 shadow-sm border">
                                                <i class="bi bi-trash3-fill text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit{{ $guru->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Edit Profil Guru</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('guru.update', $guru->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="user_id" value="{{ $guru->user_id }}">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nomor Induk Pegawai (NIP)</label>
                                                    <input type="text" name="nip" class="form-control rounded-3 py-2" value="{{ $guru->NIP }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                                    <input type="text" name="nama_guru" class="form-control rounded-3 py-2" value="{{ $guru->nama_guru }}" required>
                                                </div>
                                                <div class="p-3 bg-light rounded-3">
                                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Email akun tidak dapat diubah dari menu ini.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-4 pt-0">
                                                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/blue/no-data-found.svg" width="150" class="mb-3">
                                    <p class="text-muted">Belum ada data guru yang terdaftar.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Guru Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('guru.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Akun User Terdaftar</label>
                            <select name="user_id" class="form-select rounded-3 py-2" required>
                                <option value="" selected disabled>-- Pilih Akun --</option>
                                @php
                                    // Filter: Ambil user 'guru' yang belum punya entri di tabel guru
                                    $availableUsers = \App\Models\User::where('role', 'guru')
                                        ->whereDoesntHave('guru')
                                        ->get();
                                @endphp

                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            
                            @if($availableUsers->isEmpty())
                                <div class="mt-2 p-2 bg-danger-subtle text-danger rounded-3 small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Tidak ada akun guru yang tersedia.
                                </div>
                            @else
                                <small class="text-muted mt-1 d-block">Hanya menampilkan akun role 'guru' yang belum memiliki profil.</small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NIP</label>
                            <input type="text" name="nip" class="form-control rounded-3 py-2" placeholder="Masukkan NIP" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_guru" class="form-control rounded-3 py-2" placeholder="Masukkan Nama & Gelar" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>