<?php

namespace App\Services\Hosting;

use App\Models\HostingServer;
use App\Models\HostingService;
use Illuminate\Support\Str;
use RuntimeException;

class HostingProvisioningService
{
    public function __construct(
        protected CpanelAdapter $cpanel,
        protected CustomSshAdapter $ssh,
    ) {}

    public function provision(HostingService $hostingService, string $domain): array
    {
        $server = HostingServer::where('is_active', true)->firstOrFail();
        $plan = $hostingService->plan;

        $rawPrefix = preg_replace('/[^a-z0-9]/', '', strtolower(explode('.', $domain)[0]));
        $cleanPrefix = substr($rawPrefix ?: 'user', 0, 10);
        $username = $cleanPrefix.rand(100, 999);
        $password = Str::random(16);

        $provider = $this->resolveProvider($server);

        $result = $provider->createAccount($server, [
            'username' => $username,
            'domain' => $domain,
            'password' => $password,
            'disk_quota_mb' => $plan->disk_space_mb,
            'max_ftp' => $plan->max_ftp,
            'max_databases' => $plan->max_databases,
            'max_emails' => $plan->max_emails,
            'max_subdomains' => $plan->max_subdomains,
        ]);

        $hostingService->update([
            'hosting_server_id' => $server->id,
            'domain' => $domain,
            'username' => $username,
            'server_ip' => $server->ip_address,
            'panel_url' => $server->panel_type === 'custom_ssh' ? 'https://'.$server->ip_address.':2222' : $server->api_url,
            'provisioned_at' => now(),
        ]);

        return $result;
    }

    public function suspend(HostingService $hostingService): void
    {
        $this->resolveProvider($hostingService->server)->suspendAccount($hostingService->server, $hostingService->username);
    }

    public function unsuspend(HostingService $hostingService): void
    {
        $this->resolveProvider($hostingService->server)->unsuspendAccount($hostingService->server, $hostingService->username);
    }

    public function terminate(HostingService $hostingService): void
    {
        $this->resolveProvider($hostingService->server)->terminateAccount($hostingService->server, $hostingService->username);
    }

    public function changePassword(HostingService $hostingService, string $password): void
    {
        $this->resolveProvider($hostingService->server)->changePassword($hostingService->server, $hostingService->username, $password);
    }

    private function resolveProvider(HostingServer $server): HostingProviderInterface
    {
        return match ($server->panel_type) {
            'custom_ssh' => $this->ssh,
            'cpanel', 'directadmin' => $this->cpanel,
            default => throw new RuntimeException("Unknown hosting provider type: {$server->panel_type}"),
        };
    }
}
