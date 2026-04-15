<x-app-layout>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-table p-4 border-0 shadow-sm" style="border-radius: 20px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Kehadiran Terbaru</h5>
                        <small class="text-muted">Daftar siswa yang baru saja melakukan scan QR</small>
                    </div>
                    <form action="{{ route('dashboard') }}" method="GET">
                        <input type="text" name="search" class="search-bar" placeholder="Cari siswa..." value="{{ request('search') }}">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>ID Siswa</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kehadiranTerbaru as $absensi)
                                {{-- Pastikan data siswa tersedia --}}
                                @if($absensi->siswa)
                                <tr>
                                    <td class="fw-bold text-primary">
                                        #{{ $absensi->siswa->NIS ?? $absensi->siswa->id }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            {{-- Avatar otomatis dari nama siswa --}}
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($absensi->siswa->nama ?? 'S') }}&background=random&color=fff" 
     class="rounded-circle me-3" 
     width="35">
                                            <span class="fw-medium">{{ $absensi->siswa->nama_siswa }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        @if($absensi->siswa->kelas)
                                            {{ $absensi->siswa->kelas->tingkat }} 
                                            {{ $absensi->siswa->kelas->jurusan }} 
                                            {{ $absensi->siswa->kelas->nomor_kelas }}
                                        @else
                                            <span class="text-muted small">Kelas belum diatur</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">
                                        {{ $absensi->created_at->format('H:i A') }}
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = [
                                                'Hadir' => 'success',
                                                'Izin'  => 'warning',
                                                'Sakit' => 'info',
                                                'Alpa'  => 'danger'
                                            ][$absensi->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge rounded-pill bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3">
                                            {{ $absensi->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('absensi.show', $absensi->id) }}" class="btn btn-light btn-sm rounded-3">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x d-block mb-2" style="font-size: 2rem;"></i>
                                    Belum ada data kehadiran hari ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>