<?php

namespace App\Http\Controllers;

use App\Models\PointRule;
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    public function index()
    {
        $pointRules = \App\Models\PointRule::all();
        $items = \App\Models\FlexibilityItem::all();
        
        // Gunakan 'with(kelas)' saja, karena relasinya langsung
        $topSiswa = \App\Models\Siswa::with('kelas')
            ->orderBy('points_store', 'desc')
            ->take(5)->get();

        $bottomSiswa = \App\Models\Siswa::with('kelas')
            ->orderBy('points_store', 'asc')
            ->take(5)->get();

        return view('gamifikasi.index', compact('pointRules', 'items', 'topSiswa', 'bottomSiswa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'target_role' => 'required|in:siswa,guru',
            'condition_operator' => 'required|in:<,>,BETWEEN,=',
            'condition_value' => 'required|string', // Contoh: 07:00:00
            'point_modifier' => 'required|integer', // Contoh: 5 atau -5
        ]);

        PointRule::create($validated);
        return redirect()->back()->with('success', 'Aturan poin berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $rule = PointRule::findOrFail($id);
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'target_role' => 'required|in:siswa,guru',
            'condition_operator' => 'required|in:<,>,BETWEEN,=',
            'condition_value' => 'required|string',
            'point_modifier' => 'required|integer',
        ]);

        $rule->update($validated);
        return redirect()->back()->with('success', 'Aturan poin berhasil diperbarui.');
    }

    public function destroy($id)
    {
        PointRule::destroy($id);
        return redirect()->back()->with('success', 'Aturan berhasil dihapus.');
    }
}