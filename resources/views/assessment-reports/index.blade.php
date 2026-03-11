<x-app-layout>
    @section('title', 'Laporan Penilaian Siswa')

    <div class="card card-table p-4 border-0 shadow-sm" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Laporan Penilaian Siswa</h5>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.8rem;">
                        <i class="bi bi-calendar-check-fill me-1"></i> 
                        Periode: {{ $tahunAktif->tahun ?? 'Semua Periode' }} {{ isset($tahunAktif->semester) ? '('.$tahunAktif->semester.')' : '' }}
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('laporan-penilaian.export') }}?format=excel" class="btn btn-success px-4 shadow-sm" style="border-radius: 12px;">
                    <i class="bi bi-file-excel me-2"></i> Export Excel
                </a>
                <a href="{{ route('laporan-penilaian.export') }}?format=pdf" class="btn btn-danger px-4 shadow-sm" style="border-radius: 12px;">
                    <i class="bi bi-file-pdf me-2"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="small fw-bold">Tahun Ajaran</label>
                <select class="form-select rounded-3" id="tahunAjaranFilter">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" {{ request('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->tahun }} ({{ $year->semester }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Tingkat</label>
                <select class="form-select rounded-3" id="tingkatFilter">
                    <option value="">Semua</option>
                    <option value="10" {{ request('tingkat') == 10 ? 'selected' : '' }}>10</option>
                    <option value="11" {{ request('tingkat') == 11 ? 'selected' : '' }}>11</option>
                    <option value="12" {{ request('tingkat') == 12 ? 'selected' : '' }}>12</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Jurusan</label>
                <select class="form-select rounded-3" id="jurusanFilter">
                    <option value="">Semua Jurusan</option>
                    @foreach($majors as $major)
                        <option value="{{ $major }}" {{ request('jurusan') == $major ? 'selected' : '' }}>{{ $major }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Kelas</label>
                <select class="form-select rounded-3" id="kelasFilter">
                    <option value="">Semua Kelas</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100 rounded-3" onclick="applyFilter()">
                    <i class="bi bi-filter me-2"></i> Terapkan
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th class="text-center">Total Penilaian</th>
                        <th class="text-center">Rata-rata</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $siswa)
                    @php
                        $assessments = $siswa->assessmentsReceived ?? collect();
                        $totalScore = $assessments->flatMap(function($a) {
                            return $a->details;
                        })->avg('score') ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($siswa->name) }}&background=0047ff&color=fff" class="rounded-circle me-2" width="32">
                                <span class="fw-bold">{{ $siswa->name }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted">{{ $siswa->siswa->nis ?? '-' }}</span></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $siswa->anggotaKelas->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                {{ $assessments->count() }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold {{ $totalScore >= 75 ? 'text-success' : ($totalScore >= 60 ? 'text-warning' : 'text-danger') }}">
                                {{ number_format($totalScore, 1) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('laporan-penilaian.show', $siswa->id) }}" class="btn btn-sm btn-outline-primary rounded-3">
                                <i class="bi bi-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            Belum ada data penilaian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function applyFilter() {
            const tahunAjaran = document.getElementById('tahunAjaranFilter').value;
            const tingkat = document.getElementById('tingkatFilter').value;
            const jurusan = document.getElementById('jurusanFilter').value;
            const kelas = document.getElementById('kelasFilter').value;
            
            let url = new URL(window.location.href);
            
            if (tahunAjaran) url.searchParams.set('tahun_ajaran_id', tahunAjaran);
            else url.searchParams.delete('tahun_ajaran_id');
            
            if (tingkat) url.searchParams.set('tingkat', tingkat);
            else url.searchParams.delete('tingkat');
            
            if (jurusan) url.searchParams.set('jurusan', jurusan);
            else url.searchParams.delete('jurusan');
            
            if (kelas) url.searchParams.set('kelas_id', kelas);
            else url.searchParams.delete('kelas_id');
            
            window.location.href = url.toString();
        }

        // Update kelas dropdown ketika tingkat/jurusan berubah
        document.getElementById('tingkatFilter').addEventListener('change', updateKelas);
        document.getElementById('jurusanFilter').addEventListener('change', updateKelas);

        function updateKelas() {
            const tingkat = document.getElementById('tingkatFilter').value;
            const jurusan = document.getElementById('jurusanFilter').value;
            
            fetch(`/laporan-penilaian/filter?tingkat=${tingkat}&jurusan=${jurusan}&ajax=1`)
                .then(response => response.json())
                .then(data => {
                    const kelasSelect = document.getElementById('kelasFilter');
                    kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                    
                    data.classes.forEach(kelas => {
                        kelasSelect.innerHTML += `<option value="${kelas.id}">${kelas.nama_kelas}</option>`;
                    });
                });
        }
    </script>
    @endpush
</x-app-layout>