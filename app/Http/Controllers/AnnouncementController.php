<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index()
    {
        $announcements = Announcement::with('creator')->latest()->paginate(10);
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create()
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        return view('admin.announcements.create', compact('units'));
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:umum,akademik,kepegawaian,penting',
            'publish_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'sometimes|boolean',
            'attachment' => 'nullable|file|max:2048', // max 2MB
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'title.max' => 'Judul pengumuman tidak boleh lebih dari 255 karakter.',
            'content.required' => 'Isi pengumuman wajib diisi.',
            'category.required' => 'Kategori pengumuman wajib dipilih.',
            'category.in' => 'Kategori pengumuman yang dipilih tidak valid.',
            'publish_date.date' => 'Format tanggal terbit tidak valid.',
            'expiry_date.date' => 'Format tanggal berakhir tidak valid.',
            'expiry_date.after_or_equal' => 'Tanggal berakhir harus sama dengan atau setelah tanggal terbit.',
            'attachment.file' => 'File lampiran harus berupa file yang valid.',
            'attachment.max' => 'Ukuran file lampiran tidak boleh lebih dari 2MB.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        // Handle attachment file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('announcements', 'public');
            $validated['attachment'] = $path;
        }

        // Map dynamic target units from grid input
        $targetUnits = [];
        if ($request->has('units') && is_array($request->input('units'))) {
            foreach ($request->input('units') as $unitId => $unitData) {
                if (isset($unitData['enabled']) && $unitData['enabled'] == '1') {
                    $selectedAudiences = $unitData['audiences'] ?? [];
                    if (empty($selectedAudiences) || count($selectedAudiences) === 5) {
                        $targetAudience = 'global';
                    } else {
                        $targetAudience = implode(',', $selectedAudiences);
                    }
                    $targetUnits[] = [
                        'school_unit_id' => (int)$unitId,
                        'target_audience' => $targetAudience
                    ];
                }
            }
        }
        $validated['target_units'] = $targetUnits;

        $announcement = Announcement::create($validated);

        // Sync to targeted school units
        $this->syncAnnouncementToUnits($announcement);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diterbitkan.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        $units = SchoolUnit::where('is_active', true)->get();
        return view('admin.announcements.show', compact('announcement', 'units'));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement)
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        return view('admin.announcements.edit', compact('announcement', 'units'));
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:umum,akademik,kepegawaian,penting',
            'publish_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'sometimes|boolean',
            'attachment' => 'nullable|file|max:2048', // max 2MB
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'title.max' => 'Judul pengumuman tidak boleh lebih dari 255 karakter.',
            'content.required' => 'Isi pengumuman wajib diisi.',
            'category.required' => 'Kategori pengumuman wajib dipilih.',
            'category.in' => 'Kategori pengumuman yang dipilih tidak valid.',
            'publish_date.date' => 'Format tanggal terbit tidak valid.',
            'expiry_date.date' => 'Format tanggal berakhir tidak valid.',
            'expiry_date.after_or_equal' => 'Tanggal berakhir harus sama dengan atau setelah tanggal terbit.',
            'attachment.file' => 'File lampiran harus berupa file yang valid.',
            'attachment.max' => 'Ukuran file lampiran tidak boleh lebih dari 2MB.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle attachment file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);
            }
            $path = $request->file('attachment')->store('announcements', 'public');
            $validated['attachment'] = $path;
        }

        // Map dynamic target units from grid input
        $targetUnits = [];
        if ($request->has('units') && is_array($request->input('units'))) {
            foreach ($request->input('units') as $unitId => $unitData) {
                if (isset($unitData['enabled']) && $unitData['enabled'] == '1') {
                    $selectedAudiences = $unitData['audiences'] ?? [];
                    if (empty($selectedAudiences) || count($selectedAudiences) === 5) {
                        $targetAudience = 'global';
                    } else {
                        $targetAudience = implode(',', $selectedAudiences);
                    }
                    $targetUnits[] = [
                        'school_unit_id' => (int)$unitId,
                        'target_audience' => $targetAudience
                    ];
                }
            }
        }
        $validated['target_units'] = $targetUnits;

        $announcement->update($validated);

        // Sync to targeted school units
        $this->syncAnnouncementToUnits($announcement);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diubah.');
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement)
    {
        // Delete from school units first
        $this->syncAnnouncementToUnits($announcement, 'delete');

        // Delete attachment file
        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Sync announcement target data to school units.
     */
    private function syncAnnouncementToUnits(Announcement $announcement, $action = 'sync')
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $targetUnits = $announcement->target_units ?? [];
        $targetUnitIds = collect($targetUnits)->pluck('school_unit_id')->toArray();

        foreach ($units as $unit) {
            $isTarget = in_array($unit->id, $targetUnitIds);

            // If action is delete, or this unit is not a target anymore, send DELETE
            if ($action === 'delete' || !$isTarget) {
                try {
                    Http::withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->post(rtrim($unit->api_url, '/') . '/sync/announcements', [
                        'action' => 'delete',
                        'central_id' => $announcement->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to delete announcement from unit {$unit->name}: " . $e->getMessage());
                }
            } else {
                // If it is a target, find the target audience chosen for this unit
                $targetInfo = collect($targetUnits)->firstWhere('school_unit_id', $unit->id);
                $targetAudience = $targetInfo['target_audience'] ?? 'global';

                // Resolve attachment URL if exists
                $attachmentUrl = $announcement->attachment ? asset('storage/' . $announcement->attachment) : null;

                try {
                    Http::withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->post(rtrim($unit->api_url, '/') . '/sync/announcements', [
                        'action' => 'sync',
                        'central_id' => $announcement->id,
                        'title' => $announcement->title,
                        'content' => $announcement->content,
                        'category' => $announcement->category,
                        'target_audience' => $targetAudience,
                        'publish_date' => $announcement->publish_date ? $announcement->publish_date->format('Y-m-d H:i:s') : null,
                        'expiry_date' => $announcement->expiry_date ? $announcement->expiry_date->format('Y-m-d H:i:s') : null,
                        'is_active' => $announcement->is_active,
                        'attachment' => $attachmentUrl,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to sync announcement to unit {$unit->name}: " . $e->getMessage());
                }
            }
        }
    }
}
