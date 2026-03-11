<?php

namespace App\Http\Controllers;

use App\Models\AssessmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssessmentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = AssessmentCategory::withCount('details')->get();
        return view('penilaian-siswa.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('penilaian-siswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:assessment_categories,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        AssessmentCategory::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('penilaian-siswa.index')
            ->with('success', 'Kategori penilaian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = AssessmentCategory::with('details')->findOrFail($id);
        return view('penilaian-siswa.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        return view('penilaian-siswa.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = AssessmentCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:assessment_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('penilaian-siswa.index')
            ->with('success', 'Kategori penilaian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        
        // Cek apakah kategori memiliki detail penilaian
        if ($category->details()->count() > 0) {
            return redirect()->route('penilaian-siswa.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki detail penilaian.');
        }

        $category->delete();

        return redirect()->route('penilaian-siswa.index')
            ->with('success', 'Kategori penilaian berhasil dihapus.');
    }

    /**
     * Remove multiple resources in storage.
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        
        if (!$ids) {
            return redirect()->route('penilaian-siswa.index')
                ->with('error', 'Tidak ada data yang dipilih.');
        }

        // Cek apakah ada kategori yang memiliki detail penilaian
        $categoriesWithDetails = AssessmentCategory::whereIn('id', $ids)
            ->has('details')
            ->count();

        if ($categoriesWithDetails > 0) {
            return redirect()->route('penilaian-siswa.index')
                ->with('error', 'Beberapa kategori tidak dapat dihapus karena masih memiliki detail penilaian.');
        }

        AssessmentCategory::whereIn('id', $ids)->delete();

        return redirect()->route('penilaian-siswa.index')
            ->with('success', 'Data kategori penilaian berhasil dihapus.');
    }
}