<x-app-layout>
    @section('title', 'Manajemen Kelas')

    <div class="card card-table p-4 border-0 shadow-sm" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Daftar Kelas</h5>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                        <i class="bi bi-calendar-check-fill me-1"></i> 
                        Periode Aktif: {{ $tahunAktif->tahun ?? 'Belum Diatur' }} ({{ $tahunAktif->semester ?? '-' }})
                    </span>
                </div>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                <i class="bi bi-plus-lg me-2"></i> Tambah Kelas
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th width="150">Tingkat</th>
                        <th>Jurusan</th>
                        <th>Nomor/Nama Kelas</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $kelas)
                    <tr>
                        <td>
                            <div class="fw-bold badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-3">
                                Kelas {{ $kelas->tingkat }}
                            </div>
                        </td>
                        <td><span class="fw-bold text-dark">{{ $kelas->jurusan }}</span></td>
                        <td><span class="text-muted fw-medium">{{ $kelas->nomor_kelas }}</span></td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm rounded-3">
                                <button class="btn btn-white btn-sm border" data-bs-toggle="modal" data-bs-target="#modalEditKelas{{ $kelas->id }}">
                                    <i class="bi bi-pencil-square text-warning"></i>
                                </button>
                                <form action="{{ route('kelas.destroy', $kelas->id) }}" method="POST" onsubmit="return confirm('Hapus data kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-white btn-sm border">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditKelas{{ $kelas->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold">Edit Data Kelas</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('kelas.update', $kelas->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="small fw-bold">Tingkat</label>
                                                <select name="tingkat" class="form-select rounded-3" required>
                                                    <option value="10" {{ $kelas->tingkat == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="11" {{ $kelas->tingkat == 11 ? 'selected' : '' }}>11</option>
                                                    <option value="12" {{ $kelas->tingkat == 12 ? 'selected' : '' }}>12</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="small fw-bold">Nomor/Nama Kelas</label>
                                                <input type="text" name="nomor_kelas" class="form-control rounded-3" value="{{ $kelas->nomor_kelas }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="small fw-bold">Jurusan</label>
                                                <input type="text" name="jurusan" class="form-control rounded-3" value="{{ $kelas->jurusan }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTambahKelas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small fw-bold">Tingkat</label>
                                <select name="tingkat" class="form-select rounded-3" required>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="small fw-bold">Nomor/Nama Kelas</label>
                                <input type="text" name="nomor_kelas" class="form-control rounded-3" placeholder="Contoh: 1 atau A" required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold">Jurusan</label>
                                <input type="text" name="jurusan" class="form-control rounded-3" placeholder="Contoh: PPLG / TKJ" required>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded-3">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Kelas yang ditambahkan akan otomatis mengikuti tahun ajaran yang sedang aktif.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>