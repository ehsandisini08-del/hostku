<?php

namespace App\Services\Hosting;

use App\Models\HostingServer;

interface HostingProviderInterface
{
    public function createAccount(HostingServer $server, array $config): array;

    public function suspendAccount(HostingServer $server, string $username): array;

    public function unsuspendAccount(HostingServer $server, string $username): array;

    public function terminateAccount(HostingServer $server, string $username): array;

    public function changePassword(HostingServer $server, string $username, string $password): array;

    public function getAccountInfo(HostingServer $server, string $username): array;

    public function getResourceUsage(HostingServer $server, string $username): array;
}
