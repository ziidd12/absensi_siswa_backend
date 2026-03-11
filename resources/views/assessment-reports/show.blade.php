<x-app-layout>
    @section('title', 'Detail Laporan - ' . $siswa->name)

    <div class="card card-table p-4 border-0 shadow-sm" style="border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Detail Laporan Penilaian</h5>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-person-fill me-1"></i> {{ $siswa->name }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill shadow-sm ms-2">
                        <i class="bi bi-door-open-fill me-1"></i> {{ $siswa->siswa->kelas->nama_kelas ?? 'Kelas tidak ditemukan' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('laporan-penilaian.index') }}" class="btn btn-outline-secondary px-4 shadow-sm" style="border-radius: 12px;">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="bg-light p-4 rounded-3">
                    <h6 class="fw-bold mb-3">Rata-rata Nilai per Kategori</h6>
                    @forelse($scores as $score)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $score->name }}</span>
                            <span class="fw-bold">{{ $score->average_score }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $score->average_score }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada data penilaian</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="bg-light p-4 rounded-3">
                    <h6 class="fw-bold mb-3">Ringkasan</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Penilaian</td>
                            <td class="fw-bold">{{ $history->count() }}x</td>
                        </tr>
                        <tr>
                            <td>Nilai Tertinggi</td>
                            <td class="fw-bold text-success">{{ number_format($scores->max('average_score') ?? 0, 1) }}</td>
                        </tr>
                        <tr>
                            <td>Nilai Terendah</td>
                            <td class="fw-bold text-danger">{{ number_format($scores->min('average_score') ?? 0, 1) }}</td>
                        </tr>
                        <tr>
                            <td>Rata-rata Total</td>
                            <td class="fw-bold text-primary">{{ number_format($scores->avg('average_score') ?? 0, 1) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-light p-4 rounded-3">
            <h6 class="fw-bold mb-3">Riwayat Penilaian</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Periode</th>
                            <th>Penilai</th>
                            <th>Catatan</th>
                            <th class="text-center">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td>{{ $item->period }}</td>
                            <td>{{ $item->evaluator->name ?? '-' }}</td>
                            <td>{{ $item->general_notes ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ number_format($item->details->avg('score') ?? 0, 1) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada riwayat penilaian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>