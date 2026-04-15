@extends('layouts.app')
@section('title', 'Gamification Engine')

@section('content')
<div class="row">
    <!-- KOLOM KIRI: RULE BUILDER -->
    <div class="col-lg-8">
        <div class="card card-table p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0">Rule Builder</h5>
                    <small class="text-muted">Buat logika pemberian poin otomatis</small>
                </div>
                <span class="badge bg-light text-primary border px-3">Statement Engine v1.0</span>
            </div>

            <!-- STATEMENT BUILDER UI -->
            <form action="{{ route('gamifikasi.rules.store') }}" method="POST" class="p-4 mb-4" style="background: #f8faff; border: 2px dashed #0047ff; border-radius: 20px;">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Nama Aturan</label>
                        <input type="text" name="rule_name" class="form-control border-0 shadow-sm" placeholder="Contoh: Datang Sangat Pagi" required style="border-radius: 12px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">JIKA (Target)</label>
                        <select name="target_role" class="form-select border-0 shadow-sm" style="border-radius: 12px;">
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Kondisi</label>
                        <select name="condition_operator" id="condition_operator" class="form-select border-0 shadow-sm" style="border-radius: 12px;">
                            <option value="<">Kurang Dari (<)</option>
                            <option value=">">Lebih Dari (>)</option>
                            <option value="=">Sama Dengan (=)</option>
                        </select>
                    </div>
                    
                    <!-- NILAI PEMBANDING DYNAMIC -->
                    <div class="col-md-3" id="value_container">
                        <label class="form-label small fw-bold">Nilai Pembanding</label>
                        <input type="time" name="condition_value" class="form-control border-0 shadow-sm" required style="border-radius: 12px;">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Maka Poin</label>
                        <input type="number" name="point_modifier" class="form-control border-0 shadow-sm text-primary fw-bold" placeholder="+5 / -10" required style="border-radius: 12px;">
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 12px;">
                            <i class="bi bi-lightning-charge-fill me-2"></i>Aktifkan Aturan
                        </button>
                    </div>
                </div>
            </form>

            <!-- DAFTAR ATURAN -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase">
                        <tr>
                            <th>Aturan</th>
                            <th>Logika Statement</th>
                            <th>Poin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pointRules as $rule)
                        <tr>
                            <td>
                                <span class="fw-bold d-block">{{ $rule->rule_name }}</span>
                                <small class="text-muted">Target: {{ ucfirst($rule->target_role) }}</small>
                            </td>
                            <td>
                                <code class="text-primary fw-bold">IF {{ $rule->condition_operator }} '{{ $rule->condition_value }}'</code>
                            </td>
                            <td>
                                <span class="badge {{ $rule->point_modifier > 0 ? 'bg-light text-success' : 'bg-light text-danger' }} px-3">
                                    {{ $rule->point_modifier > 0 ? '+' : '' }}{{ $rule->point_modifier }} Pts
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('gamifikasi.rules.destroy', $rule->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light btn-sm rounded-3 border"><i class="bi bi-trash text-danger"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: ANALITIK INTEGRITAS -->
    <div class="col-lg-4">
        <!-- <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #0047ff 0%, #002d9c 100%);">
            <div class="card-body p-4 text-white">
                <h6 class="fw-bold mb-4"><i class="bi bi-award me-2"></i>Top Integrity</h6>
                @foreach($topSiswa as $ts)
                <div class="d-flex align-items-center mb-3 p-2 rounded-3" style="background: rgba(255,255,255,0.1);">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ts->nama_siswa) }}&background=fff&color=0047ff" class="rounded-circle me-3" width="35">
                    <div class="flex-grow-1">
                        <div class="small fw-bold">{{ $ts->nama_siswa }}</div>
                        <small class="opacity-75" style="font-size: 10px;">{{ $ts->kelas->tingkat }} {{ $ts->kelas->jurusan }} {{ $ts->kelas->nomor_kelas }}</small>
                    </div>
                    <div class="badge bg-white text-primary">{{ $ts->points_store }}</div>
                </div>
                @endforeach
            </div>
        </div> -->

        <div class="card card-table p-4">
            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Watchlist</h6>
            <p class="small text-muted mb-4">Siswa dengan poin terendah (Butuh pembinaan).</p>
            @foreach($bottomSiswa as $bs)
            <div class="d-flex align-items-center mb-3">
                <div class="flex-grow-1">
                    <div class="small fw-bold">{{ $bs->nama_siswa }}</div>
                    <div class="progress mt-1" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: 30%"></div>
                    </div>
                </div>
                <span class="badge bg-soft-danger text-danger ms-3">{{ $bs->points_store }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('condition_operator').addEventListener('change', function() {
        const valueContainer = document.getElementById('value_container');
        const operator = this.value;

        if (operator === '=') {
            // Jika operator sama dengan (=), ubah jadi dropdown status
            valueContainer.innerHTML = `
                <label class="form-label small fw-bold">Pilih Status</label>
                <select name="condition_value" class="form-select border-0 shadow-sm" style="border-radius: 12px;" required>
                    <option value="alpa">Alpa</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                </select>
            `;
        } else {
            // Jika operator < atau >, ubah jadi input jam (Time)
            valueContainer.innerHTML = `
                <label class="form-label small fw-bold">Waktu Batas</label>
                <input type="time" name="condition_value" step="1" class="form-control border-0 shadow-sm" required style="border-radius: 12px;">
            `;
        }
    });
</script>
@endpush

@endsection