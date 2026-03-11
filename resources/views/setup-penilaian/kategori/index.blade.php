<x-app-layout>
    @section('title', 'Kategori Penilaian')

    <div class="row">
        <div class="col-md-12">
            {{-- Alert Success --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Indikator & Kategori</h5>
                        <small class="text-muted">Kelola dimensi penilaian dan butir pertanyaan</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Kategori
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Kategori</th>
                                <th>Jumlah Indikator</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $index => $cat)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $cat->name }}</div>
                                        <small class="text-muted">{{ Str::limit($cat->description, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary px-3 py-2" style="background-color: #eef2ff;">
                                            {{ $cat->questions_count ?? 0 }} Indikator
                                        </span>
                                    </td>
                                    <td>
                                        @if($cat->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3">Aktif</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- Link ke Pertanyaan --}}
                                            <a href="{{ route('setup-penilaian.pertanyaan.index', ['category_id' => $cat->id]) }}" 
                                               class="btn btn-light btn-sm rounded-3 shadow-sm border" title="Kelola Indikator">
                                                <i class="bi bi-list-check text-primary"></i>
                                            </a>
                                            
                                            {{-- Tombol Edit --}}
                                            <button class="btn btn-light btn-sm rounded-3 shadow-sm border" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $cat->id }}">
                                                <i class="bi bi-pencil-square text-warning"></i>
                                            </button>

                                            {{-- Form Hapus --}}
                                            <form action="{{ route('setup-penilaian.kategori.destroy', $cat->id) }}" method="POST" class="d-inline">
                                                @csrf 
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm rounded-3 shadow-sm border" onclick="return confirm('Hapus kategori ini?')">
                                                    <i class="bi bi-trash3-fill text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- MODAL EDIT (Wajib di dalam @forelse agar variabel $cat terbaca) --}}
                                <div class="modal fade" id="modalEditKategori{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                            <div class="modal-header border-0 p-4 pb-0">
                                                <h5 class="fw-bold">Edit Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('setup-penilaian.kategori.update', $cat->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Nama Kategori</label>
                                                        <input type="text" name="name" class="form-control rounded-3" value="{{ $cat->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Deskripsi Singkat</label>
                                                        <textarea name="description" class="form-control rounded-3" rows="3">{{ $cat->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status</label>
                                                        <select name="is_active" class="form-select rounded-3">
                                                            <option value="1" {{ $cat->is_active ? 'selected' : '' }}>Aktif</option>
                                                            <option value="0" {{ !$cat->is_active ? 'selected' : '' }}>Non-Aktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 p-4 pt-0">
                                                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- END MODAL EDIT --}}

                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada kategori penilaian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH (Di luar loop karena tidak butuh data baris tertentu) --}}
    <div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Buat Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('setup-penilaian.kategori.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Kategori</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Kedisiplinan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deskripsi Singkat</label>
                            <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Jelaskan apa yang dinilai di kategori ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>