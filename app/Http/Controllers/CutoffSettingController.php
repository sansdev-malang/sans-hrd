<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CutoffSettingController extends Controller
{
    /**
     * Display the cutoff settings form.
     */
    public function index()
    {
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 26);
        return view('settings.index', compact('cutoffDate'));
    }

    /**
     * Update the cutoff settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'payroll_cutoff_date' => 'required|integer|min:1|max:31',
        ]);

        Setting::set('payroll_cutoff_date', $request->input('payroll_cutoff_date'));

        return redirect()->back()->with('success', 'Pengaturan cut-off berhasil diperbarui!');
    }
}
