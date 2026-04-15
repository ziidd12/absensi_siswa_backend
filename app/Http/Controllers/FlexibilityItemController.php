<?php

namespace App\Http\Controllers;

use App\Models\FlexibilityItem;
use Illuminate\Http\Request;

class FlexibilityItemController extends Controller
{
    public function index()
    {
        $items = FlexibilityItem::all();
        return view('marketplace.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'point_cost'  => 'required|integer|min:1',
            'stock_limit' => 'required|integer|min:0',
        ]);

        FlexibilityItem::create($validated);
        return redirect()->back()->with('success', 'Item marketplace berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = FlexibilityItem::findOrFail($id);
        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'point_cost'  => 'required|integer|min:1',
            'stock_limit' => 'required|integer|min:0',
        ]);

        $item->update($validated);
        return redirect()->back()->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy($id)
    {
        FlexibilityItem::destroy($id);
        return redirect()->back()->with('success', 'Item berhasil dihapus.');
    }
}