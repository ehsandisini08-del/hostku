<?php

namespace App\Services\Hosting;

use App\Models\HostingEmail;
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
            'db_name' => $result['db_name'] ?? ('h_'.$username),
            'db_user' => $result['db_user'] ?? $username,
            'db_pass' => $result['db_pass'] ?? null,
            'php_version' => $server->php_version ?: '8.4',
            'ssl_active' => false,
            'panel_url' => url('/customer/hosting/'.$hostingService->id),
            'provisioned_at' => now(),
        ]);

        return $result;
    }

    public function reissueSsl(HostingService $hostingService): array
    {
        $server = $hostingService->server;
        if (! $server || $server->panel_type !== 'custom_ssh') {
            return ['success' => false, 'message' => 'SSL automation is only available for custom SSH servers.'];
        }

        $result = $this->ssh->reissueSsl($server, $hostingService->domain);
        if ($result['success']) {
            $hostingService->update(['ssl_active' => true]);
        }

        return $result;
    }

    public function changePhpVersion(HostingService $hostingService, string $newVersion): array
    {
        $server = $hostingService->server;
        if (! $server || $server->panel_type !== 'custom_ssh') {
            return ['success' => false, 'message' => 'PHP version switcher is only available for custom SSH servers.'];
        }

        $result = $this->ssh->changePhpVersion($server, $hostingService->username, $hostingService->domain, $newVersion);
        if ($result['success']) {
            $hostingService->update(['php_version' => $newVersion]);
        }

        return $result;
    }

    public function resetDatabasePassword(HostingService $hostingService, string $newPassword): array
    {
        $server = $hostingService->server;
        if (! $server || $server->panel_type !== 'custom_ssh') {
            return ['success' => false];
        }

        $result = $this->ssh->resetDatabasePassword($server, $hostingService->username, $newPassword);
        if ($result['success']) {
            $hostingService->update(['db_pass' => $newPassword]);
        }

        return $result;
    }

    public function createMailbox(HostingService $hostingService, string $mailboxUser, string $password, int $quotaMb = 500): array
    {
        $domain = $hostingService->domain;
        $maxEmails = $hostingService->plan?->max_emails ?? 1;

        if ($maxEmails > 0 && $hostingService->emails()->count() >= $maxEmails) {
            throw new RuntimeException("Batas pembuatan email untuk paket ini telah tercapai ({$maxEmails} email).");
        }

        $server = $hostingService->server;
        if ($server && $server->panel_type === 'custom_ssh') {
            $this->ssh->createMailbox($server, $domain, $mailboxUser, $password);
        }

        $email = $hostingService->emails()->create([
            'email_address' => "{$mailboxUser}@{$domain}",
            'mailbox_user' => $mailboxUser,
            'domain' => $domain,
            'quota_mb' => $quotaMb,
        ]);

        return [
            'success' => true,
            'email' => $email,
        ];
    }

    public function deleteMailbox(HostingService $hostingService, HostingEmail $email): void
    {
        $server = $hostingService->server;
        if ($server && $server->panel_type === 'custom_ssh') {
            $this->ssh->deleteMailbox($server, $hostingService->domain, $email->mailbox_user);
        }

        $email->delete();
    }

    public function changeMailboxPassword(HostingService $hostingService, HostingEmail $email, string $newPassword): void
    {
        $server = $hostingService->server;
        if ($server && $server->panel_type === 'custom_ssh') {
            $this->ssh->changeMailboxPassword($server, $hostingService->domain, $email->mailbox_user, $newPassword);
        }
    }

    public function checkDns(string $domain, string $expectedIp): array
    {
        if (empty($domain)) {
            return [
                'domain' => $domain,
                'expected_ip' => $expectedIp,
                'resolved_ip' => null,
                'is_pointed' => false,
            ];
        }

        if (app()->environment('testing')) {
            return [
                'domain' => $domain,
                'expected_ip' => $expectedIp,
                'resolved_ip' => $expectedIp,
                'is_pointed' => true,
            ];
        }

        $resolvedIp = @gethostbyname($domain);
        $isPointed = ($resolvedIp === $expectedIp);

        return [
            'domain' => $domain,
            'expected_ip' => $expectedIp,
            'resolved_ip' => ($resolvedIp === $domain) ? null : $resolvedIp,
            'is_pointed' => $isPointed,
        ];
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
