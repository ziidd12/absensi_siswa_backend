<x-app-layout>
    @section('title', 'Manajemen User')

    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Pengaturan Akun</h5>
                        <small class="text-muted">Kelola akses login untuk Admin, Guru, dan Siswa</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-person-plus-fill me-2"></i> Buat User Baru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Nama / Email</th>
                                <th>Role</th>
                                <th>Device ID</th>
                                <th>Terdaftar</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? $user->email) }}&background=0047ff&color=fff" class="rounded-circle me-3" width="35">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name ?? 'User Baru' }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger-subtle text-danger px-3 rounded-pill text-capitalize">Admin</span>
                                    @elseif($user->role == 'guru')
                                        <span class="badge bg-primary-subtle text-primary px-3 rounded-pill text-capitalize">Guru</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 rounded-pill text-capitalize">Siswa</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->device_id)
                                        <code class="small text-primary">{{ substr($user->device_id, 0, 8) }}...</code>
                                    @else
                                        <span class="text-muted small italic">Not Linked</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-light btn-sm rounded-3 border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $user->id }}" title="Edit User">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </button>
                                        
                                        @if($user->role == 'siswa')
                                        <form action="{{ route('admin.users.reset_device', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 border shadow-sm" onclick="return confirm('Reset Device ID siswa ini?')" title="Reset Device">
                                                <i class="bi bi-phone-flip text-info"></i>
                                            </button>
                                        </form>
                                        @endif

                                        @if($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 border shadow-sm" onclick="return confirm('Hapus akun ini?')" title="Hapus User">
                                                <i class="bi bi-trash3 text-danger"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Update Akun</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control rounded-3" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Email</label>
                                                    <input type="email" name="email" class="form-control rounded-3" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Password Baru (Kosongkan jika tidak ganti)</label>
                                                    <input type="password" name="password" class="form-control rounded-3" placeholder="******">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Role Akses</label>
                                                    <select name="role" class="form-select rounded-3">
                                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="guru" {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                                                        <option value="siswa" {{ $user->role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-4 pt-0">
                                                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Update Akun</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
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
                    <h5 class="fw-bold">Buat User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Masukkan nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Alamat Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="email@sekolah.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Role / Peran</label>
                            <select name="role" class="form-select rounded-3" required>
                                <option value="" selected disabled>Pilih Role...</option>
                                <option value="admin">Admin</option>
                                <option value="guru">Guru</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>