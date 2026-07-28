<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\ZktecoDevice;
use App\Models\AttendanceLog;
use App\Models\AdmsCommand;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ZkTecoAdmsController extends Controller
{
    /**
     * Middleware to check ADMS status and Token
     */
    public function __construct()
    {
        // ADMS Check can also be done inside methods if needed
    }

    private function verifyAdmsAccess(Request $request)
    {
        $enabled = Setting::get('adms_enabled', '0');
        if ($enabled !== '1' && $enabled !== true) {
            return false;
        }

        $expectedToken = Setting::get('adms_auth_token');
        if (!empty($expectedToken)) {
            // Check auth token (often passed in Auth header or query string)
            // ZKTeco typically doesn't send Bearer, it might send it in headers or query
            // We'll check the 'Auth' or 'auth' query parameter just in case, or HTTP_AUTHORIZATION
            $requestToken = $request->header('Authorization') 
                            ?? $request->query('Auth') 
                            ?? $request->query('auth');
            
            // simple check
            if ($requestToken !== $expectedToken && $requestToken !== 'Token ' . $expectedToken) {
                return false;
            }
        }

        return true;
    }

    /**
     * GET /iclock/cdata
     * Handshake / Initialization from the device
     */
    public function handshake(Request $request)
    {
        Log::info('ADMS Handshake Request: ' . $request->fullUrl());

        if (!$this->verifyAdmsAccess($request)) {
            Log::warning('ADMS Handshake Unauthorized');
            return response('Unauthorized', 401);
        }

        $sn = $request->query('SN');
        if (!$sn) {
            return response('OK', 200); // Standard fallback
        }

        // We can update the device last_sync_at here
        $device = ZktecoDevice::where('sn', $sn)->first();
        if ($device) {
            $device->update([
                'is_online' => true,
                'last_sync_at' => now(),
            ]);
        } else {
            Log::warning("ADMS Handshake from unknown SN: {$sn}");
        }

        // Return expected ZKTeco parameters
        // Registry=OK indicates successful handshake
        $response = "GET OPTION FROM: {$sn}\n"
                  . "Stamp=8888\n"
                  . "OpStamp=8888\n"
                  . "ErrorDelay=60\n"
                  . "Delay=30\n"
                  . "TransTimes=00:00;14:00\n"
                  . "TransInterval=1\n"
                  . "TransFlag=11111111\n"
                  . "Realtime=1\n"
                  . "Encrypt=0";

        return response($response, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * POST /iclock/cdata
     * Receive attendance data (TSV) from the device
     */
    public function receiveData(Request $request)
    {
        Log::info('ADMS Receive Data Request: ' . $request->fullUrl());
        if (!$this->verifyAdmsAccess($request)) {
            return response('Unauthorized', 401);
        }

        $sn = $request->query('SN');
        $device = ZktecoDevice::where('sn', $sn)->first();

        // If device not found, we still return OK so device clears its queue, 
        // or we could return ERROR. ZKTeco expects "OK: \n".
        if (!$device) {
            Log::warning("ADMS Data received from unknown SN: {$sn}");
            return response("OK\n", 200)->header('Content-Type', 'text/plain');
        }

        // Update device status
        $device->update([
            'is_online' => true,
            'last_sync_at' => now(),
        ]);

        $content = $request->getContent();
        
        // Example format: 101    2026-07-27 07:15:00    1    1
        $lines = explode("\n", trim($content));
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // ZKTeco separates columns by Tab (\t)
            $parts = explode("\t", $line);
            
            if (count($parts) >= 2) {
                $pin = trim($parts[0]);
                $timestampStr = trim($parts[1]);
                $state = isset($parts[2]) ? trim($parts[2]) : 0; // In/Out state (if available)

                try {
                    $timestamp = Carbon::parse($timestampStr);
                    
                    // We only store the UID (PIN) from the machine. 
                    // The child unit applications (SD, SMP, PAUD) will fetch these logs via API
                    // and map the UID to their local Employee records.
                    $exists = AttendanceLog::where('zkteco_device_id', $device->id)
                        ->where('uid', $pin)
                        ->where('timestamp', $timestamp->format('Y-m-d H:i:s'))
                        ->exists();

                    if (!$exists) {
                        AttendanceLog::create([
                            'zkteco_device_id' => $device->id,
                            'uid' => $pin,
                            'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                            'state' => $state,
                            'type' => 1 // ADMS push
                        ]);
                        $count++;
                    }
                } catch (\Exception $e) {
                    Log::error("ADMS Parse Error for SN {$sn}: " . $e->getMessage());
                }
            }
        }

        Log::info("ADMS Synced {$count} records from {$device->name} (SN: {$sn})");

        // The device expects exactly this response to clear its queue
        return response("OK\n", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * GET /iclock/getrequest
     * Device asking for commands (Foundation for future remote control)
     */
    public function getRequest(Request $request)
    {
        if (!$this->verifyAdmsAccess($request)) {
            return response('Unauthorized', 401);
        }

        $sn = $request->query('SN');
        
        // Update device status
        $device = ZktecoDevice::where('sn', $sn)->first();
        if ($device) {
            $device->update([
                'is_online' => true,
                'last_sync_at' => now(),
            ]);
        }

        // Fetch pending commands for this device
        if ($device) {
            $commands = AdmsCommand::where('zkteco_device_id', $device->id)
                ->where('status', 'pending')
                ->get();
                
            if ($commands->count() > 0) {
                $responseStr = "";
                foreach ($commands as $cmd) {
                    // Format: C:<ID>:<Command String>
                    $responseStr .= "C:{$cmd->id}:{$cmd->command_string}\n";
                }
                return response($responseStr, 200)->header('Content-Type', 'text/plain');
            }
        }

        // Return empty OK since we don't have pending commands yet
        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * POST /iclock/devicecmd
     * Device reporting the result of a command execution
     */
    public function deviceCmd(Request $request)
    {
        if (!$this->verifyAdmsAccess($request)) {
            return response('Unauthorized', 401);
        }

        $sn = $request->query('SN');
        
        $device = ZktecoDevice::where('sn', $sn)->first();
        if ($device) {
            $device->update([
                'is_online' => true,
                'last_sync_at' => now(),
            ]);
        }

        $content = $request->getContent();
        // Example format: ID=123&Return=0
        // Or it might be sent as application/x-www-form-urlencoded
        
        // ZKTeco sometimes sends raw body like: ID=123&Return=0
        // Sometimes it sends in query string or standard post. Let's parse both.
        $id = $request->input('ID');
        $returnCode = $request->input('Return');

        if (!$id && $content) {
            parse_str(trim($content), $parsedBody);
            $id = $parsedBody['ID'] ?? null;
            $returnCode = $parsedBody['Return'] ?? null;
        }

        if ($id) {
            $command = AdmsCommand::find($id);
            if ($command) {
                // Return=0 usually means success
                $command->update([
                    'status' => ($returnCode == 0 || $returnCode === "0") ? 'success' : 'failed'
                ]);
            }
        }

        return response("OK", 200)->header('Content-Type', 'text/plain');
    }
}
