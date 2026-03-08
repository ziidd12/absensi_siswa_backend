<x-app-layout>
    @section('title', 'Tahun Ajaran')

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
                        <h5 class="fw-bold mb-0">Data Tahun Ajaran</h5>
                        <small class="text-muted">Kelola periode aktif untuk absensi siswa</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambahTA">
                        <i class="bi bi-plus-circle-fill me-2"></i> Tambah Periode
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Tahun Ajaran</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $ta)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-calendar3 me-2 text-primary"></i> {{ $ta->tahun }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $ta->semester }}</span>
                                </td>
                                <td>
                                    @if($ta->is_active)
                                        <span class="badge bg-success-subtle text-success px-3 rounded-pill">
                                            <i class="bi bi-check2-circle me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted px-3 rounded-pill border">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditTA{{ $ta->id }}" title="Edit">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditTA{{ $ta->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Update Periode</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('tahun-ajaran.update', $ta->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Tahun (Contoh: 2024/2025)</label>
                                                    <input type="text" name="tahun" class="form-control rounded-3" value="{{ $ta->tahun }}" required placeholder="202X/202X">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Semester</label>
                                                    <select name="semester" class="form-select rounded-3" required>
                                                        <option value="Ganjil" {{ $ta->semester == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                                        <option value="Genap" {{ $ta->semester == 'Genap' ? 'selected' : '' }}>Genap</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Status Aktif</label>
                                                    <select name="is_active" class="form-select rounded-3" required>
                                                        <option value="1" {{ $ta->is_active ? 'selected' : '' }}>Aktif</option>
                                                        <option value="0" {{ !$ta->is_active ? 'selected' : '' }}>Non-Aktif</option>
                                                    </select>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahTA" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Periode Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tahun-ajaran.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tahun Ajaran</label>
                            <input type="text" name="tahun" class="form-control rounded-3" placeholder="Contoh: 2025/2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Semester</label>
                            <select name="semester" class="form-select rounded-3" required>
                                <option value="" selected disabled>Pilih Semester...</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jadikan Aktif?</label>
                            <select name="is_active" class="form-select rounded-3" required>
                                <option value="0">Tidak (Non-Aktif)</option>
                                <option value="1">Ya (Aktifkan Sekarang)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>