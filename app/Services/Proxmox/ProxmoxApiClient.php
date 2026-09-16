<?php

namespace App\Services\Proxmox;

use App\Models\ProxmoxServer;
use Illuminate\Support\Facades\Http;

class ProxmoxApiClient
{
    public function get(ProxmoxServer $server, string $endpoint): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "PVEAPIToken={$server->token_id}=".($server->token_secret ?? ''))
            ->get("{$this->baseUrl($server)}/{$endpoint}");

        return $response->json() ?: [];
    }

    public function post(ProxmoxServer $server, string $endpoint, array $data = []): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 60])
            ->withHeader('Authorization', "PVEAPIToken={$server->token_id}=".($server->token_secret ?? ''))
            ->post("{$this->baseUrl($server)}/{$endpoint}", $data);

        return $response->json() ?: [];
    }

    public function delete(ProxmoxServer $server, string $endpoint): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "PVEAPIToken={$server->token_id}=".($server->token_secret ?? ''))
            ->delete("{$this->baseUrl($server)}/{$endpoint}");

        return $response->json() ?: [];
    }

    private function baseUrl(ProxmoxServer $server): string
    {
        return rtrim($server->host, '/').':'.$server->port.'/api2/json';
    }
}
