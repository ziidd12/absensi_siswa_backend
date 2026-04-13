<x-app-layout>
    @section('title', 'Jadwal Pelajaran')

    <div class="row">
        <div class="col-md-12">
            {{-- Alert Notifikasi --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan pada input data.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-table p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Jadwal Pelajaran</h5>
                        <small class="text-muted">Atur waktu kegiatan belajar mengajar berdasarkan kelas</small>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-calendar-plus me-2"></i> Tambah Jadwal
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Kelas</th>
                                <th>Hari & Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $jadwal)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $jadwal->mapel->nama_mapel }}</div>
                                    <small class="text-muted">{{ $jadwal->mapel->kode_mapel ?? 'Kode Mapel' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($jadwal->guru->nama_guru) }}&background=6f42c1&color=fff" class="rounded-circle me-3" width="35">
                                        {{ $jadwal->guru->nama_guru }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3">
                                        {{ $jadwal->kelas->tingkat }} {{ $jadwal->kelas->jurusan }} {{ $jadwal->kelas->nomor_kelas }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-primary fw-bold">{{ $jadwal->hari }}</div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i> 
                                        {{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-light btn-sm rounded-3 shadow-sm border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit{{ $jadwal->id }}">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                        </button>
                                        
                                        <form action="{{ route('jadwal.destroy', $jadwal->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm rounded-3 shadow-sm border">
                                                <i class="bi bi-trash3-fill text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="modalEdit{{ $jadwal->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Edit Jadwal</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('jadwal.update', $jadwal->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Mata Pelajaran</label>
                                                    <select name="mapel_id" class="form-select rounded-3" required>
                                                        @foreach($mapel as $m)
                                                            <option value="{{ $m->id }}" {{ $jadwal->mapel_id == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Guru</label>
                                                    <select name="guru_id" class="form-select rounded-3" required>
                                                        @foreach($guru as $g)
                                                            <option value="{{ $g->id }}" {{ $jadwal->guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small fw-bold">Kelas</label>
                                                        <select name="kelas_id" class="form-select rounded-3" required>
                                                            @foreach($kelas as $k)
                                                                <option value="{{ $k->id }}" {{ $jadwal->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->tingkat }} {{ $k->jurusan }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label small fw-bold">Hari</label>
                                                        <select name="hari" class="form-select rounded-3" required>
                                                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                                                <option value="{{ $h }}" {{ $jadwal->hari == $h ? 'selected' : '' }}>{{ $h }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label small fw-bold">Jam Mulai</label>
                                                        <input type="time" name="jam_mulai" class="form-control rounded-3" value="{{ $jadwal->jam_mulai }}" required>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <label class="form-label small fw-bold">Jam Selesai</label>
                                                        <input type="time" name="jam_selesai" class="form-control rounded-3" value="{{ $jadwal->jam_selesai }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-4 pt-0">
                                                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada jadwal pelajaran yang dibuat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Tambah Jadwal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('jadwal.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Mapel --</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Guru</label>
                            <select name="guru_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Pilih Guru --</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Pilih Kelas</label>
                                <select name="kelas_id" class="form-select rounded-3" required>
                                    <option value="" selected disabled>-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->tingkat }} {{ $k->jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Hari</label>
                                <select name="hari" class="form-select rounded-3" required>
                                    <option value="" selected disabled>-- Pilih Hari --</option>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control rounded-3" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control rounded-3" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>