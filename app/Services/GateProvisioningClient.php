<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GateProvisioningClient
{
    protected string $gateUrl;

    protected ?string $clientId;

    protected ?string $clientSecret;

    public function __construct()
    {
        $this->gateUrl = rtrim(config('services.gate.url', 'https://gate.sabira-iibs.id'), '/');
        $this->clientId = config('services.gate.client_id');
        $this->clientSecret = config('services.gate.client_secret');
    }

    protected function getHeaders(): array
    {
        return [
            'X-Client-Id' => $this->clientId ?? '',
            'X-Client-Secret' => $this->clientSecret ?? '',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Fetch canonical users list from Gate SSO Provisioning API.
     */
    public function fetchCanonicalUsers(): array
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception('Kredensial SSO_CLIENT_ID dan SSO_CLIENT_SECRET belum dikonfigurasi.');
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get("{$this->gateUrl}/api/provisioning/users");

            if (! $response->successful()) {
                Log::error('Gagal mengambil data user dari Gate SSO Provisioning API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Gagal terhubung ke Gate SSO Provisioning API (HTTP '.$response->status().').');
            }

            $data = $response->json();

            if (! isset($data['users']) || ! is_array($data['users'])) {
                throw new Exception('Format respon dari Gate SSO Provisioning API tidak valid.');
            }

            return $data['users'];
        } catch (Exception $e) {
            Log::error('Exception saat memanggil Gate Provisioning API: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Post sync results back to Gate SSO Provisioning API.
     */
    public function sendSyncResults(array $items): bool
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            Log::warning('Sinkronisasi selesai tapi kredensial Gate SSO tidak lengkap untuk mengirim laporan hasil.');

            return false;
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->post("{$this->gateUrl}/api/provisioning/sync-results", [
                    'items' => $items,
                ]);

            if (! $response->successful()) {
                Log::error('Gagal melaporkan hasil sync ke Gate SSO', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Exception saat mengirim hasil sync ke Gate SSO: '.$e->getMessage());

            return false;
        }
    }
}
