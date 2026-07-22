<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BioTimeService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('ZKBIO_URL', 'http://127.0.0.1:8080'), '/');
        $this->username = env('ZKBIO_USERNAME', 'admin');
        $this->password = env('ZKBIO_PASSWORD', 'admin');
    }

    /**
     * Authenticate and get JWT Token
     */
    public function authenticate(): bool
    {
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/jwt-api-token-auth/', [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $this->token = $response->json('token');
                return true;
            }

            Log::error("ZKBio Time Auth Failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("ZKBio Time Auth Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transactions from ZKBio Time
     * @param string|null $startTime (e.g. '2026-07-22 00:00:00')
     * @param string|null $endTime (e.g. '2026-07-22 23:59:59')
     * @return array
     */
    public function getTransactions(?string $startTime = null, ?string $endTime = null): array
    {
        if (!$this->token) {
            if (!$this->authenticate()) {
                return [];
            }
        }

        $params = [];
        if ($startTime) {
            $params['punch_time__gte'] = $startTime;
        }
        if ($endTime) {
            $params['punch_time__lte'] = $endTime;
        }
        
        $params['page_size'] = 10000;

        try {
            $allTransactions = [];
            $url = $this->baseUrl . '/iclock/api/transactions/';

            while ($url) {
                $response = Http::withToken($this->token)
                    ->timeout(30)
                    ->get($url, $params);

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? [];
                    $allTransactions = array_merge($allTransactions, $data);

                    // Handle pagination
                    $url = $json['next'] ?? null;
                    $params = []; // params are included in the 'next' URL provided by ZKBioTime
                } else {
                    Log::error("ZKBio Time Fetch Transactions Failed: " . $response->body());
                    break;
                }
            }

            return $allTransactions;
        } catch (\Exception $e) {
            Log::error("ZKBio Time Fetch Transactions Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get terminals from ZKBio Time
     */
    public function getTerminals(): array
    {
        if (!$this->token) {
            if (!$this->authenticate()) {
                return [];
            }
        }

        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->get($this->baseUrl . '/iclock/api/terminals/');

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            Log::error("ZKBio Time Fetch Terminals Failed: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("ZKBio Time Fetch Terminals Exception: " . $e->getMessage());
            return [];
        }
    }
}
