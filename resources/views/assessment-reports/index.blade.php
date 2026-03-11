<x-app-layout>
    @section('title', 'Laporan Penilaian Siswa')

    <div class="container-fluid py-4">
        <div class="card border border-light-subtle shadow-none p-4" style="border-radius: 20px; background-color: white;">
            
            <div class="mb-4">
                <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">Laporan Penilaian Siswa</h4>
                <p class="text-muted small mb-0">Pantau hasil evaluasi karakter siswa berdasarkan data penilaian terbaru.</p>
            </div>

            <div class="row g-3 mb-4 p-4 bg-light rounded-4 border-0">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Tahun Ajaran</label>
                    <select class="form-select border-0 shadow-sm" id="tahunAjaranFilter" style="border-radius: 10px; height: 45px;">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year->id }}" {{ request('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->tahun }} ({{ $year->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Jurusan</label>
                    <select class="form-select border-0 shadow-sm" id="jurusanFilter" style="border-radius: 10px; height: 45px;">
                        <option value="">Semua Jurusan</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}" {{ request('jurusan') == $major ? 'selected' : '' }}>{{ $major }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Cari Nama Siswa</label>
                    <input type="text" class="form-control border-0 shadow-sm" id="searchFilter" placeholder="Masukkan nama..." value="{{ request('search') }}" style="border-radius: 10px; height: 45px;">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100 shadow-none d-flex align-items-center justify-content-center gap-2" 
                            onclick="applyFilter()" 
                            style="border-radius: 10px; background-color: #1E5EFF; border: none; height: 45px; font-weight: 600;">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead class="bg-white">
                        <tr class="text-secondary small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            <th class="border-0 py-3 px-3">Nama Siswa</th>
                            <th class="border-0 py-3">NIS</th>
                            <th class="border-0 py-3">Kelas</th>
                            <th class="border-0 py-3 text-center">Sesi Nilai</th>
                            <th class="border-0 py-3 text-center">Rata-rata</th>
                            <th class="border-0 py-3 text-end px-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $siswa)
                        @php
                            $assessments = $siswa->assessmentsReceived ?? collect();
                            $avgScore = $assessments->flatMap(function($a) {
                                return $a->details;
                            })->avg('score') ?? 0;
                        @endphp
                        <tr>
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center rounded-3 me-3" 
                                         style="width: 42px; height: 42px; border: 1px solid rgba(30, 94, 255, 0.1);">
                                        {{ substr($siswa->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0" style="font-size: 14px;">{{ $siswa->name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $siswa->email ?? 'Siswa' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-muted font-monospace small">{{ $siswa->siswa->NIS ?? '-' }}</span></td>
                            <td>
                                <span class="badge bg-white text-dark border fw-medium px-2 py-1" style="border-radius: 6px;">
                                    {{ $siswa->siswa->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $assessments->count() }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-block px-3 py-1 rounded-pill fw-bold" 
                                     style="font-size: 13px; {{ $avgScore >= 7.5 ? 'background-color: #e6f4ea; color: #1e7e34;' : ($avgScore >= 6.0 ? 'background-color: #fff4e5; color: #b45d00;' : 'background-color: #fce8e8; color: #c62828;') }}">
                                    {{ number_format($avgScore, 1) }}
                                </div>
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('monitoring-nilai.show', $siswa->id) }}" 
                                   class="btn btn-light btn-sm border-0 rounded-3 px-3 py-2 text-primary" 
                                   style="font-weight: 600;">
                                    Detail <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <p class="text-muted">Tidak ada data siswa ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $data->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function applyFilter() {
            const tahunAjaran = document.getElementById('tahunAjaranFilter').value;
            const jurusan = document.getElementById('jurusanFilter').value;
            const search = document.getElementById('searchFilter').value;
            
            let url = new URL(window.location.href);
            
            if (tahunAjaran) url.searchParams.set('tahun_ajaran_id', tahunAjaran);
            else url.searchParams.delete('tahun_ajaran_id');
            
            if (jurusan) url.searchParams.set('jurusan', jurusan);
            else url.searchParams.delete('jurusan');

            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-app-layout>