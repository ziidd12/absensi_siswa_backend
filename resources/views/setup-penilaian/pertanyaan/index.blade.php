<x-app-layout>
    @section('title', 'Indikator Penilaian')

    <div class="mb-4">
        <a href="{{ route('setup-penilaian.kategori.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Kategori
        </a>
        <h4 class="fw-bold mt-2">Kategori: {{ $category->name }}</h4>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                <h6 class="fw-bold mb-3">Tambah Indikator Baru</h6>
                <form action="{{ route('setup-penilaian.pertanyaan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $category->id }}">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pertanyaan/Indikator</label>
                        <textarea name="question_text" class="form-control rounded-3" rows="4" placeholder="Contoh: Seberapa sering siswa datang tepat waktu?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bobot (Opsional)</label>
                        <input type="number" name="weight" class="form-control rounded-3" value="1" min="1">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i> Tambah ke Daftar
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                <h6 class="fw-bold mb-4">Daftar Butir Penilaian</h6>
                <div class="list-group list-group-flush">
                    @forelse($questions as $q)
                        <div class="list-group-item px-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 14px;">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <p class="mb-0 text-dark fw-medium">{{ $q->question_text }}</p>
                                    <small class="text-muted">Bobot: {{ $q->weight }}</small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle border" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                    <li><a class="dropdown-item small" href="#"><i class="bi bi-pencil me-2"></i> Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('setup-penilaian.pertanyaan.destroy', $q->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item small text-danger" onclick="return confirm('Hapus indikator?')">
                                                <i class="bi bi-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <img src="https://illustrations.popsy.co/blue/empty-box.svg" width="150" class="mb-3">
                            <p class="text-muted">Belum ada indikator untuk kategori ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>