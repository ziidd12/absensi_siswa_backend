<x-app-layout>
    @section('title', 'Detail Laporan - ' . $user->name)

    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('monitoring-nilai.index') }}" 
                   class="btn btn-white border shadow-sm rounded-circle p-0 d-flex align-items-center justify-content-center" 
                   style="width: 45px; height: 45px; background: white;">
                    <i class="bi bi-arrow-left fs-5 text-dark"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">Detail Laporan Penilaian</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('monitoring-nilai.index') }}" class="text-decoration-none text-primary">Laporan</a></li>
                            <li class="breadcrumb-item active">{{ $user->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 20px;">
                    <div class="mx-auto bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle mb-3" 
                         style="width: 80px; height: 80px; font-size: 30px; border: 4px solid #f0f7ff;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->siswa->NIS ?? 'NIS tidak tersedia' }}</p>
                    
                    <hr class="my-4 opacity-50">
                    
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Kelas</label>
                            <span class="fw-medium text-dark">{{ $user->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Jurusan</label>
                            <span class="fw-medium text-dark">{{ $user->siswa->kelas->jurusan ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                    <h6 class="fw-bold text-dark mb-4">Analisis Karakter Siswa</h6>
                    
                    @if($scores->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-graph-up-arrow fs-1 text-light-emphasis mb-3 d-block"></i>
                            <p class="text-muted">Belum ada data penilaian untuk siswa ini di tahun ajaran ini.</p>
                        </div>
                    @else
                        <div style="height: 350px;">
                            <canvas id="radarChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!$scores->isEmpty())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('radarChart').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: {!! json_encode($scores->pluck('name')) !!},
                datasets: [{
                    label: 'Skor Rata-rata',
                    data: {!! json_encode($scores->pluck('average_score')) !!},
                    fill: true,
                    backgroundColor: 'rgba(30, 94, 255, 0.2)',
                    borderColor: '#1E5EFF',
                    pointBackgroundColor: '#1E5EFF',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#1E5EFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 10,
                        ticks: { stepSize: 2 }
                    }
                }
            }
        });
    </script>
    @endpush
    @endif
</x-app-layout>