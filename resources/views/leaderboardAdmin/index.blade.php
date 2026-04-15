@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #f8f9fa; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-1">🏆 Ranking Kehadiran Siswa</h2>
                    <p class="text-muted">Data dumasar kana akumulasi poin prestasi sareng kehadiran.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-primary shadow-sm px-4 py-2" style="border-radius: 50px; font-size: 1rem;">
                        <i class="bi bi-calendar3 me-2"></i> {{ $tanggal }}
                    </span>
                </div>
            </div>

            <div class="row mb-5 align-items-end justify-content-center">
                @php $top3 = $leaderboard->take(3); @endphp
                
                @if(isset($top3[1])) {{-- Juara 2 --}}
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-center" style="border-radius: 20px; background: linear-gradient(135.47deg, #e2e2e2 0%, #c0c0c0 100%);">
                        <div class="card-body py-4">
                            <div class="h1">🥈</div>
                            <h5 class="fw-bold text-dark mb-0">{{ $top3[1]->nama }}</h5>
                            <p class="small text-dark opacity-75">{{ $top3[1]->kelas->nama_full ?? '-' }}</p>
                            <div class="badge bg-white text-dark px-3">{{ number_format($top3[1]->points_store) }} Pts</div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($top3[0])) {{-- Juara 1 --}}
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow text-center" style="border-radius: 20px; background: linear-gradient(135.47deg, #FFD700 0%, #FFA500 100%); transform: scale(1.1); z-index: 2;">
                        <div class="card-body py-5">
                            <div class="h1">🥇</div>
                            <h4 class="fw-bold text-dark mb-0">{{ $top3[0]->nama }}</h4>
                            <p class="text-dark opacity-75">{{ $top3[0]->kelas->nama_full ?? '-' }}</p>
                            <div class="badge bg-white text-dark px-4 py-2" style="font-size: 1.1rem;">{{ number_format($top3[0]->points_store) }} Pts</div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($top3[2])) {{-- Juara 3 --}}
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm text-center" style="border-radius: 20px; background: linear-gradient(135.47deg, #CD7F32 0%, #A0522D 100%);">
                        <div class="card-body py-4 text-white">
                            <div class="h1">🥉</div>
                            <h5 class="fw-bold mb-0">{{ $top3[2]->nama }}</h5>
                            <p class="small opacity-75">{{ $top3[2]->kelas->nama_full ?? '-' }}</p>
                            <div class="badge bg-white text-dark px-3">{{ number_format($top3[2]->points_store) }} Pts</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Rank</th>
                                <th class="py-3 border-0">Siswa</th>
                                <th class="py-3 border-0">Kelas</th>
                                <th class="py-3 border-0 text-end px-4">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderboard as $index => $s)
                            <tr>
                                <td class="px-4 fw-bold text-muted">#{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                            {{ substr($s->nama, 0, 1) }}
                                        </div>
                                        <span class="fw-bold">{{ $s->nama }}</span>
                                    </div>
                                </td>
                                <td>{{ $s->kelas->nama_full ?? '-' }}</td>
                                <td class="text-end px-4">
                                    <span class="fw-bold text-primary">{{ number_format($s->points_store) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection