@extends('layouts.app')
@section('title', 'Reward Shop Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Reward Marketplace</h4>
        <small class="text-muted">Kelola item kelonggaran untuk siswa</small>
    </div>
    <button class="btn btn-primary px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-2"></i> Tambah Item
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3">
        {{ session('success') }}
    </div>
@endif

<div class="row">
    @forelse($items as $item)
    <div class="col-md-4 mb-4">
        <div class="card card-table h-100 overflow-hidden border-0 shadow-sm" style="border-radius: 20px;">
            <div class="p-4 text-center bg-primary text-white">
                <i class="bi bi-ticket-perforated" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3 mb-0">{{ $item->item_name }}</h5>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <h3 class="fw-bold text-primary mb-0">{{ number_format($item->point_cost) }}</h3>
                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px;">Poin</small>
                </div>
                <div class="badge bg-light text-dark border px-3 py-2 mb-4">
                    Stok Tersedia: {{ $item->stock_limit }}
                </div>
                <div class="d-flex gap-2">
                    <!-- Tombol Edit -->
                    <button class="btn btn-light border w-100 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}">
                        <i class="bi bi-pencil-square me-1 text-warning"></i> Edit
                    </button>
                    <!-- Tombol Hapus -->
                    <form action="{{ route('marketplace.items.destroy', $item->id) }}" method="POST" class="w-100" onsubmit="return confirm('Hapus item reward ini?')">
                        @csrf 
                        @method('DELETE')
                        <button class="btn btn-light border w-100 rounded-3 text-danger shadow-sm">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT ITEM -->
    <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Edit Item Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('marketplace.items.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Item</label>
                            <input type="text" name="item_name" class="form-control rounded-3" value="{{ $item->item_name }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Harga Poin</label>
                                <input type="number" name="point_cost" class="form-control rounded-3" value="{{ $item->point_cost }}" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Limit Stok</label>
                                <input type="number" name="stock_limit" class="form-control rounded-3" value="{{ $item->stock_limit }}" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted">Belum ada item marketplace.</div>
    </div>
    @endforelse
</div>

<!-- MODAL TAMBAH ITEM -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Tambah Item Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('marketplace.items.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Item</label>
                        <input type="text" name="item_name" class="form-control rounded-3" placeholder="Contoh: Voucher Bebas Terlambat" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Harga Poin</label>
                            <input type="number" name="point_cost" class="form-control rounded-3" placeholder="Contoh: 50" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Limit Stok</label>
                            <input type="number" name="stock_limit" class="form-control rounded-3" placeholder="Contoh: 10" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection