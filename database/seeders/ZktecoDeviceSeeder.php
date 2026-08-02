<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ZktecoDevice;

class ZktecoDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                "name" => "Mesin Absensi 1",
                "sn" => "HDP1240100015",
                "ip_address" => "-",
                "port" => 4370,
                "model_name" => "ZKTeco K40",
                "location" => "Lobi Depan",
                "sync_interval" => 5,
            ],
            [
                "name" => "Mesin Absensi 2",
                "sn" => "CN96212660316",
                "ip_address" => "-",
                "port" => 4370,
                "model_name" => "ZKTeco K40",
                "location" => "Lobi Belakang",
                "sync_interval" => 5,
            ],
        ];

        foreach ($devices as $device) {
            ZktecoDevice::firstOrCreate(["ip_address" => $device["ip_address"]], $device);
        }
    }
}
