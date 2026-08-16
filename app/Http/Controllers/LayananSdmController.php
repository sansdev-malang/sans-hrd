<?php

namespace App\Http\Controllers;

use App\Models\SchoolUnit;
use Illuminate\Http\Request;

class LayananSdmController extends Controller
{
    /**
     * Display the HR service helpdesk dashboard with mock data.
     */
    public function index()
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        return view('layanan-sdm.index', compact('units'));
    }
}
