@extends('layouts.app')
@section('title', 'Inventory Token Siswa')

@section('content')
<div class="card card-table p-4">
    <div class="mb-4">
        <h5 class="fw-bold mb-0">Inventory Token & Voucher</h5>
        <small class="text-muted">Monitoring penggunaan token kelonggaran oleh siswa.</small>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="bg-light small text-muted">
                <tr>
                    <th>Siswa</th>
                    <th>Item Token</th>
                    <th>Tanggal Beli</th>
                    <th>Status</th>
                    <th>Digunakan Pada</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tokens as $t)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $t->siswa->nama_siswa }}</div>
                        <small class="text-muted">{{ $t->siswa->kelas->nama_kelas ?? '-' }}</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-ticket-fill text-primary me-2"></i>
                            <span class="fw-bold">{{ $t->item->item_name }}</span>
                        </div>
                    </td>
                    <td>{{ $t->created_at->format('d M Y') }}</td>
                    <td>
                        @if($t->status == 'AVAILABLE')
                            <span class="badge bg-success px-3">Tersedia</span>
                        @else
                            <span class="badge bg-secondary px-3">Sudah Digunakan</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $t->used_at ? $t->used_at->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection