<?php

namespace App\Http\Controllers;

use App\Models\ZktecoDevice;
use App\Services\ZktecoService;
use Illuminate\Http\Request;

class ZktecoDeviceController extends Controller
{
    /**
     * Display a listing of the Zkteco devices.
     */
    public function index()
    {
        $devices = ZktecoDevice::orderBy('name')->get();
        return view('zkteco-devices.index', compact('devices'));
    }

    /**
     * Store a newly created Zkteco device in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sn' => 'nullable|string|max:255|unique:zkteco_devices,sn',
            'ip_address' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'model_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'sync_interval' => 'required|integer|min:1',
        ]);

        $validated['is_online'] = false; // default false until connection verified

        ZktecoDevice::create($validated);

        return redirect()->route('zkteco-devices.index')
            ->with('success', 'Perangkat ZKTeco baru berhasil ditambahkan.');
    }

    /**
     * Update the specified Zkteco device in storage.
     */
    public function update(Request $request, ZktecoDevice $zktecoDevice)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sn' => 'nullable|string|max:255|unique:zkteco_devices,sn,' . $zktecoDevice->id,
            'ip_address' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'model_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'sync_interval' => 'required|integer|min:1',
        ]);

        $zktecoDevice->update($validated);

        return redirect()->route('zkteco-devices.index')
            ->with('success', 'Perangkat ZKTeco berhasil diperbarui.');
    }

    /**
     * Remove the specified Zkteco device from storage.
     */
    public function destroy(ZktecoDevice $zktecoDevice)
    {
        $zktecoDevice->delete();

        return redirect()->route('zkteco-devices.index')
            ->with('success', 'Perangkat ZKTeco berhasil dihapus.');
    }


    /**
     * Pull logs from the device manually.
     */
    public function pullLogs(ZktecoDevice $zktecoDevice, ZktecoService $zktecoService)
    {
        $result = $zktecoService->pullLogs($zktecoDevice);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Force ADMS device to push all logs by queueing a DATA UPDATE ATTLOG command.
     */
    public function forceAdms(ZktecoDevice $zktecoDevice)
    {
        \App\Models\AdmsCommand::create([
            'zkteco_device_id' => $zktecoDevice->id,
            'command_string' => 'DATA UPDATE ATTLOG',
            'status' => 'pending'
        ]);

        return redirect()->back()
            ->with('success', 'Perintah "Force ADMS" telah dikirim. Mesin akan segera mem-push semua data absen lamanya secara otomatis dalam beberapa menit ke depan.');
    }
}
