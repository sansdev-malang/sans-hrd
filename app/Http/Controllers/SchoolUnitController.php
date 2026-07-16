<?php

namespace App\Http\Controllers;

use App\Models\SchoolUnit;
use Illuminate\Http\Request;

class SchoolUnitController extends Controller
{
    /**
     * Display a listing of the school units.
     */
    public function index()
    {
        $units = SchoolUnit::orderBy('name')->get();
        return view('school-units.index', compact('units'));
    }

    /**
     * Store a newly created school unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_url' => 'required|url|max:255',
            'api_token' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        SchoolUnit::create($validated);

        return redirect()->route('school-units.index')
            ->with('success', 'Unit sekolah berhasil ditambahkan.');
    }

    /**
     * Update the specified school unit in storage.
     */
    public function update(Request $request, SchoolUnit $schoolUnit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_url' => 'required|url|max:255',
            'api_token' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $schoolUnit->update($validated);

        return redirect()->route('school-units.index')
            ->with('success', 'Unit sekolah berhasil diperbarui.');
    }

    /**
     * Remove the specified school unit from storage.
     */
    public function destroy(SchoolUnit $schoolUnit)
    {
        $schoolUnit->delete();

        return redirect()->route('school-units.index')
            ->with('success', 'Unit sekolah berhasil dihapus.');
    }
}
