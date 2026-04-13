<x-app-layout>
    @section('title', 'Data Anggota Kelas')

    <div class="row">
        <div class="col-md-12">
            {{-- Alert Success --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Alert Error (Untuk validasi khusus seperti siswa sudah ada di kelas) --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Daftar Anggota Kelas</h5>
                        <small class="text-muted">Manajemen penempatan siswa ke dalam kelas masing-masing</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Anggota
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu Bergabung</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($item->siswa->nama_siswa) }}&background=0d6efd&color=fff" class="rounded-circle me-3" width="35">
                                        <div>
                                            <div class="fw-bold">{{ $item->siswa->nama_siswa }}</div>
                                            <small class="text-muted">NIS: {{ $item->siswa->NIS }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3">
                                        {{ $item->kelas->tingkat }} {{ $item->kelas->jurusan }} {{ $item->kelas->nomor_kelas }}
                                    </span>
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-light btn-sm rounded-3 shadow-sm border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit{{ $item->id }}">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </button>
                                        
                                        <form action="{{ route('anggota-kelas.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Keluarkan siswa ini dari kelas?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 shadow-sm border">
                                                <i class="bi bi-trash3-fill text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Edit Penempatan Kelas</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('anggota-kelas.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Nama Siswa</label>
                                                    <select name="siswa_id" class="form-select rounded-3" required>
                                                        @foreach($siswa as $s)
                                                            <option value="{{ $s->id }}" {{ $item->siswa_id == $s->id ? 'selected' : '' }}>
                                                                {{ $s->nama_siswa }} ({{ $s->NIS }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Pindah ke Kelas</label>
                                                    <select name="kelas_id" class="form-select rounded-3" required>
                                                        @foreach($kelas as $k)
                                                            <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>
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
                                <td colspan="4" class="text-center py-5 text-muted">Belum ada siswa yang ditempatkan di kelas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Anggota Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('anggota-kelas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Siswa</label>
                            <select name="siswa_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama_siswa }} ({{ $s->NIS }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Kelas</label>
                            <select name="kelas_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->tingkat }} {{ $k->jurusan }} {{ $k->nomor_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>