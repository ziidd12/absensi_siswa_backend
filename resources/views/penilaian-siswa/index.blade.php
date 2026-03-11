<x-app-layout>
    @section('title', 'Manajemen Penilaian Siswa')

    <div class="card card-table p-4 border-0 shadow-sm" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Kategori Penilaian</h5>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                        <i class="bi bi-tags-fill me-1"></i> 
                        Total Kategori: {{ $categories->count() ?? 0 }}
                    </span>
                </div>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="bi bi-plus-lg me-2"></i> Tambah Kategori
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Detail</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input checkbox-item" type="checkbox" value="{{ $category->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-3">
                                {{ $category->name }}
                            </div>
                        </td>
                        <td>
                            <span class="text-muted fw-medium">{{ $category->description ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                <i class="bi bi-list-check me-1"></i> {{ $category->details_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm rounded-3">
                                <button class="btn btn-white btn-sm border" data-bs-toggle="modal" data-bs-target="#modalDetailKategori{{ $category->id }}">
                                    <i class="bi bi-eye-fill text-info"></i>
                                </button>
                                <button class="btn btn-white btn-sm border" data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $category->id }}">
                                    <i class="bi bi-pencil-square text-warning"></i>
                                </button>
                                <form action="{{ route('penilaian-siswa.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori penilaian ini? Semua detail penilaian yang terkait juga akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-white btn-sm border">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Detail Kategori -->
                    <div class="modal fade" id="modalDetailKategori{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold">Detail Kategori Penilaian</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                                <i class="bi bi-tag-fill text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $category->name }}</h6>
                                                <p class="text-muted mb-0">{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-light p-3 rounded-3">
                                            <h6 class="fw-bold mb-3">Detail Penilaian</h6>
                                            @if($category->details && $category->details->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Aspek Penilaian</th>
                                                                <th>Bobot (%)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($category->details as $detail)
                                                                <tr>
                                                                    <td>{{ $detail->aspek_penilaian }}</td>
                                                                    <td><span class="badge bg-primary">{{ $detail->bobot }}%</span></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted text-center py-3 mb-0">Belum ada detail penilaian</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="button" class="btn btn-secondary w-100 rounded-3 py-2 fw-bold shadow-sm" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Kategori -->
                    <div class="modal fade" id="modalEditKategori{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold">Edit Kategori Penilaian</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('penilaian-siswa.update', $category->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="small fw-bold">Nama Kategori</label>
                                                <input type="text" name="name" class="form-control rounded-3" value="{{ $category->name }}" placeholder="Contoh: Pengetahuan, Keterampilan" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="small fw-bold">Deskripsi</label>
                                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Deskripsi kategori penilaian...">{{ $category->description }}</textarea>
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
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            Belum ada data kategori penilaian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->count() > 0)
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <div>
                <form action="{{ route('penilaian-siswa.destroyMultiple') }}" method="POST" id="deleteMultipleForm">
                    @csrf
                    <input type="hidden" name="ids" id="selectedIds">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-3" onclick="deleteSelected()" id="deleteSelectedBtn" disabled>
                        <i class="bi bi-trash me-1"></i> Hapus Terpilih
                    </button>
                </form>
            </div>
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i> 
                Kategori yang memiliki detail penilaian tidak dapat dihapus
            </small>
        </div>
        @endif
    </div>

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="modalTambahKategori" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Kategori Penilaian Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('penilaian-siswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="small fw-bold">Nama Kategori</label>
                                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" placeholder="Contoh: Pengetahuan, Keterampilan, Sikap" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold">Deskripsi</label>
                                <textarea name="description" class="form-control rounded-3 @error('description') is-invalid @enderror" rows="3" placeholder="Deskripsi kategori penilaian...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i> 
                                Kategori akan digunakan untuk mengelompokkan aspek-aspek penilaian.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.getElementsByClassName('checkbox-item');
            for(let checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
            toggleDeleteButton();
        });

        // Individual checkbox change
        const checkboxes = document.getElementsByClassName('checkbox-item');
        for(let checkbox of checkboxes) {
            checkbox.addEventListener('change', toggleDeleteButton);
        }

        function toggleDeleteButton() {
            const checkboxes = document.getElementsByClassName('checkbox-item');
            let checkedCount = 0;
            for(let checkbox of checkboxes) {
                if(checkbox.checked) checkedCount++;
            }
            
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            if(deleteBtn) {
                deleteBtn.disabled = checkedCount === 0;
            }
        }

        function deleteSelected() {
            const checkboxes = document.getElementsByClassName('checkbox-item');
            const selectedIds = [];
            
            for(let checkbox of checkboxes) {
                if(checkbox.checked) {
                    selectedIds.push(checkbox.value);
                }
            }
            
            if(selectedIds.length === 0) {
                alert('Pilih minimal satu data untuk dihapus.');
                return;
            }
            
            if(confirm('Hapus ' + selectedIds.length + ' data kategori penilaian terpilih? Kategori yang memiliki detail penilaian tidak akan terhapus.')) {
                document.getElementById('selectedIds').value = JSON.stringify(selectedIds);
                document.getElementById('deleteMultipleForm').submit();
            }
        }

        // Show error messages in modal if any
        @if($errors->any())
            var myModal = new bootstrap.Modal(document.getElementById('modalTambahKategori'));
            myModal.show();
        @endif
    </script>
    @endpush
</x-app-layout>