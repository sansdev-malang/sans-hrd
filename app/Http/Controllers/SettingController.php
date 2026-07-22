<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $cutoffDate = Setting::get('payroll_cutoff_date', 26);
        return view('settings.index', compact('cutoffDate'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'payroll_cutoff_date' => 'required|integer|min:1|max:31',
        ]);

        Setting::set('payroll_cutoff_date', $request->payroll_cutoff_date);

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
