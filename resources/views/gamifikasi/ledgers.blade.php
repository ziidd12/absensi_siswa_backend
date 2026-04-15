@extends('layouts.app')
@section('title', 'Buku Besar Poin')

@section('content')
<div class="card card-table p-4">
    <div class="mb-4">
        <h5 class="fw-bold mb-0">Riwayat Mutasi Poin (Ledger)</h5>
        <small class="text-muted">Seluruh log masuk/keluar poin tercatat di sini secara sistem.</small>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th>Waktu</th>
                    <th>Nama Siswa</th>
                    <th>Tipe</th>
                    <th>Nominal</th>
                    <th>Saldo Akhir</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledgers as $l)
                <tr>
                    <td class="small">{{ $l->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="fw-bold">{{ $l->siswa->nama_siswa }}</div>
                        <small class="text-muted">{{ $l->siswa->NIS }}</small>
                    </td>
                    <td>
                        @if($l->transaction_type == 'EARN')
                            <span class="badge bg-success bg-opacity-10 text-success px-3">EARN</span>
                        @elseif($l->transaction_type == 'SPEND')
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3">SPEND</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger px-3">PENALTY</span>
                        @endif
                    </td>
                    <td class="fw-bold {{ $l->amount > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $l->amount > 0 ? '+' : '' }}{{ $l->amount }}
                    </td>
                    <td class="fw-bold">{{ $l->current_balance }} Pts</td>
                    <td class="small">{{ $l->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $ledgers->links() }}
    </div>
</div>
@endsection