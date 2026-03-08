<x-app-layout>
    @section('title', 'Data Siswa')

    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Daftar Siswa</h5>
                        <small class="text-muted">Manajemen data murid dan penempatan kelas</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Siswa
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Email</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $siswa)
                            <tr>
                                <td class="fw-bold text-primary">{{ $siswa->NIS }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=0047ff&color=fff" class="rounded-circle me-3" width="35">
                                        {{ $siswa->nama_siswa }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3">
                                        {{ $siswa->kelas->tingkat }} {{ $siswa->kelas->jurusan }} {{ $siswa->kelas->nomor_kelas }}
                                    </span>
                                </td>
                                <td>{{ $siswa->user->email ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-light btn-sm rounded-3 shadow-sm border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit{{ $siswa->id }}">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </button>
                                        
                                        <form action="{{ route('siswa.destroy', $siswa->id) }}" method="POST" onsubmit="return confirm('Hapus data siswa ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 shadow-sm border">
                                                <i class="bi bi-trash3-fill text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit{{ $siswa->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Edit Data Siswa</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="user_id" value="{{ $siswa->user_id }}">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">NIS</label>
                                                    <input type="text" name="nis" class="form-control rounded-3" value="{{ $siswa->nis }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                                    <input type="text" name="nama_siswa" class="form-control rounded-3" value="{{ $siswa->nama_siswa }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Kelas</label>
                                                    <select name="kelas_id" class="form-select rounded-3" required>
                                                        @foreach(\App\Models\Kelas::all() as $k)
                                                            <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                                                {{ $k->tingkat }} {{ $k->jurusan }} {{ $k->nomor_kelas }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-4 pt-0">
                                                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada data siswa.</td>
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
                    <h5 class="fw-bold">Tambah Siswa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('siswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Akun User</label>
                            <select name="user_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Akun --</option>
                                @php
                                    $availableUsers = \App\Models\User::where('role', 'siswa')
                                        ->whereDoesntHave('siswa')
                                        ->get();
                                @endphp
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @if($availableUsers->isEmpty())
                                <small class="text-danger mt-1 d-block">Tidak ada akun siswa yang tersedia.</small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NIS</label>
                            <input type="text" name="nis" class="form-control rounded-3" placeholder="Nomor Induk Siswa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_siswa" class="form-control rounded-3" placeholder="Nama Lengkap Siswa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kelas</label>
                            <select name="kelas_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                @foreach(\App\Models\Kelas::all() as $k)
                                    <option value="{{ $k->id }}">{{ $k->tingkat }} {{ $k->jurusan }} {{ $k->nomor_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>