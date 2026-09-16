# Provisioning System

## Overview

Semua provisioning (VPS, Hosting, Domain) menggunakan **queue-based async pattern**. Tidak ada synchronous call ke external API dari request cycle.

```
Payment Success (webhook)
        │
        ▼
Fire PaymentReceived event
        │
        ▼
Listener: CreateProvisioningJob
        │
        ▼
Push to Queue (provisioning queue)
        │
        ▼
Worker picks up job
        │
        ▼
Execute provisioning (Proxmox API / cPanel API / Registrar API)
        │
        ▼
On success: Update service status → 'active', send notification
On failure: Log error, retry (max 3x), notify admin
```

---

## Proxmox VPS Provisioning

### Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    ProxmoxService                        │
│  (orchestration: selects node, builds VM config)         │
└────────────┬────────────────────────────┬────────────────┘
             │                            │
             ▼                            ▼
┌─────────────────────┐     ┌─────────────────────────┐
│   ProxmoxApiClient  │     │     NodeSelector         │
│   (HTTP client,     │     │   (picks optimal node    │
│    auth, retry,     │     │    based on resources)   │
│    error mapping)   │     │                         │
└──────────┬──────────┘     └─────────────────────────┘
           │
           ▼
┌─────────────────────┐
│  Proxmox VE API     │
│  (REST: /api2/json) │
└─────────────────────┘
```

### ProvisionVpsJob — Flow

```php
class ProvisionVpsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 30; // 30 detik antar retry

    public function handle(ProxmoxService $proxmox, AuditService $audit): void
    {
        $vpsService = VpsService::findOrFail($this->vpsServiceId);

        // 1. Create provisioning log
        $provisioningJob = ProvisioningJob::create([
            'service_type' => 'vps',
            'service_id' => $vpsService->id,
            'action' => 'create',
            'status' => 'running',
        ]);

        try {
            // 2. Select optimal node
            $node = $proxmox->selectNode($vpsService->vpsPlan);

            // 3. Generate unique VMID
            $vmid = $proxmox->getNextVmId($node);

            // 4. Build VM configuration
            $config = [
                'vmid' => $vmid,
                'name' => $vpsService->hostname,
                'cores' => $vpsService->cpu_cores,
                'memory' => $vpsService->ram_mb,
                'net0' => "virtio,bridge={$vpsService->vpsPlan->network_bridge}",
                'ostemplate' => $vpsService->os_template,
                'storage' => $vpsService->vpsPlan->storage_pool,
                'disk' => "{$vpsService->disk_mb}M",
            ];

            // 5. Create VM via Proxmox API
            $result = $proxmox->createVm($node, $config);

            // 6. Start VM
            $proxmox->startVm($node, $vmid);

            // 7. Wait for IP (polling with timeout)
            $ipAddress = $proxmox->waitForIp($node, $vmid, timeout: 120);

            // 8. Update VpsService record
            $vpsService->update([
                'proxmox_node_id' => $node->id,
                'vm_id' => $vmid,
                'ip_address' => $ipAddress,
                'vm_status' => 'running',
                'provisioned_at' => now(),
            ]);

            // 9. Update related Service status
            $vpsService->service()->update(['status' => 'active']);

            // 10. Mark provisioning job complete
            $provisioningJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => $result,
            ]);

            // 11. Send notification
            $vpsService->user->notify(new VpsProvisionedNotification($vpsService));

            // 12. Audit log
            $audit->log('create', 'vps_service', $vpsService->id);

        } catch (\Exception $e) {
            $provisioningJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Release back to queue for retry, or fail permanently
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            } else {
                // Max retries reached
                $vpsService->service()->update(['status' => 'failed']);
                // Notify admin
                AdminNotification::dispatch('VPS provisioning failed', [
                    'vps_service_id' => $vpsService->id,
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}
```

### ProxmoxService — Capabilities

```php
interface ProxmoxServiceInterface
{
    // Connection
    public function testConnection(ProxmoxServer $server): bool;

    // Nodes
    public function listNodes(ProxmoxServer $server): array;
    public function getNodeStatus(string $node): array;

    // Storage & Network
    public function listStorage(string $node): array;
    public function listNetworks(string $node): array;

    // Templates
    public function listTemplates(string $node, string $storage): array;
    public function downloadTemplate(string $node, string $template): void;

    // VM Management
    public function createVm(string $node, array $config): array;
    public function startVm(string $node, int $vmid): array;
    public function stopVm(string $node, int $vmid): array;
    public function shutdownVm(string $node, int $vmid): array;
    public function rebootVm(string $node, int $vmid): array;
    public function deleteVm(string $node, int $vmid): array;
    public function cloneVm(string $node, int $vmid, int $newId, array $options): array;

    // Status & Monitoring
    public function getVmStatus(string $node, int $vmid): array;
    public function getVmConfig(string $node, int $vmid): array;
    public function getResourceUsage(string $node, int $vmid): array;
    public function getIpAddress(string $node, int $vmid): ?string;
    public function waitForIp(string $node, int $vmid, int $timeout = 120): string;

    // Snapshots
    public function listSnapshots(string $node, int $vmid): array;
    public function createSnapshot(string $node, int $vmid, string $name): array;
    public function rollbackSnapshot(string $node, int $vmid, string $snapName): array;
    public function deleteSnapshot(string $node, int $vmid, string $snapName): array;

    // Resize
    public function resizeVm(string $node, int $vmid, array $resources): array;

    // Utility
    public function selectNode(VpsPlan $plan): ProxmoxNode;
    public function getNextVmId(string $node): int;
}
```

### Node Selection Algorithm

```php
public function selectNode(VpsPlan $plan): ProxmoxNode
{
    $nodes = ProxmoxNode::where('is_online', true)
        ->with('server')
        ->where('server.is_active', true)
        ->get();

    // Filter: enough resources for the plan
    $eligible = $nodes->filter(function ($node) use ($plan) {
        $freeCpu = $node->cpu_total - $node->cpu_used;
        $freeRam = $node->ram_total_mb - $node->ram_used_mb;
        $freeDisk = $node->disk_total_mb - $node->disk_used_mb;

        return $freeCpu >= $plan->cpu_cores
            && $freeRam >= $plan->ram_mb
            && $freeDisk >= $plan->disk_mb;
    });

    if ($eligible->isEmpty()) {
        throw new NoAvailableNodeException('No node with sufficient resources');
    }

    // Select with least resource utilization (most headroom)
    return $eligible->sortBy(function ($node) {
        return ($node->cpu_used / max($node->cpu_total, 1))
             + ($node->ram_used_mb / max($node->ram_total_mb, 1));
    })->first();
}
```

---

## Hosting Provisioning

### HostingProviderInterface

```php
interface HostingProviderInterface
{
    public function createAccount(HostingServer $server, array $config): array;
    public function suspendAccount(HostingServer $server, string $username): array;
    public function unsuspendAccount(HostingServer $server, string $username): array;
    public function terminateAccount(HostingServer $server, string $username): array;
    public function changePassword(HostingServer $server, string $username, string $password): array;
    public function getAccountInfo(HostingServer $server, string $username): array;
    public function getResourceUsage(HostingServer $server, string $username): array;
    public function listAccounts(HostingServer $server): array;
}
```

### cPanel Adapter

```php
class CpanelAdapter implements HostingProviderInterface
{
    public function createAccount(HostingServer $server, array $config): array
    {
        $response = Http::withOptions([
            'verify' => false, // use SSL properly in production
            'timeout' => 60,
        ])
        ->withHeader('Authorization', "whm {$server->api_username}:{$this->decryptToken($server->api_token)}")
        ->get("{$server->api_url}/json-api/createacct", [
            'username' => $config['username'],
            'domain' => $config['domain'],
            'password' => $config['password'] ?? Str::password(16),
            'plan' => $config['package'] ?? 'default',
            'featurelist' => 'default',
            'quota' => $config['disk_quota_mb'] ?? 1024,
            'maxftp' => $config['max_ftp'] ?? 1,
            'maxsql' => $config['max_databases'] ?? 1,
            'maxpop' => $config['max_emails'] ?? 1,
            'maxsub' => $config['max_subdomains'] ?? 0,
        ]);

        if (!$response->successful()) {
            throw new ProvisioningFailedException(
                "cPanel account creation failed: {$response->body()}"
            );
        }

        $result = $response->json();

        if (($result['result'][0]['status'] ?? 0) !== 1) {
            throw new ProvisioningFailedException(
                "cPanel error: " . ($result['result'][0]['statusmsg'] ?? 'Unknown')
            );
        }

        return $result;
    }
}
```

### ProvisionHostingJob — Flow

```
1. Select hosting server with capacity
2. Generate username + password
3. Call HostingProviderInterface::createAccount()
4. Update HostingService record (username, IP, panel URL)
5. Update Service status → 'active'
6. Send welcome email with login details
```

---

## Domain Provisioning

### DomainProviderInterface

```php
interface DomainProviderInterface
{
    public function searchDomain(string $domain): array;
    public function checkAvailability(string $domain): DomainAvailability;
    public function registerDomain(string $domain, array $contacts, array $options): array;
    public function renewDomain(string $domain, int $years): array;
    public function transferDomain(string $domain, string $authCode, array $contacts): array;
    public function getDomainInfo(string $domain): array;
    public function updateNameserver(string $domain, array $nameservers): array;
    public function getTldPricing(): array;
    public function getEppCode(string $domain): string;
}
```

### RegisterDomainJob — Flow

```
1. Validate domain availability (re-check)
2. Prepare contact information (registrant, admin, tech, billing)
3. Call DomainProviderInterface::registerDomain()
4. Update Domain record (registrar_id, registration_date, expiration_date)
5. Update Service status → 'active'
6. Save domain contacts
7. Send notification
```

### RenewDomainJob — Flow

```
1. Check domain still active at registrar
2. Call DomainProviderInterface::renewDomain()
3. Update Domain expiration_date
4. Update Service expired_at
5. Generate new invoice for next cycle
```

### Domain Availability Check

```php
class DomainService
{
    public function checkAvailability(string $domain): DomainResult
    {
        // Parse domain + TLD
        $parts = explode('.', $domain, 2);
        $tld = $parts[1];

        // Check TLD pricing exists
        $pricing = DomainPricing::where('tld', $tld)->first();
        if (!$pricing) {
            return DomainResult::unsupported("TLD .{$tld} tidak tersedia");
        }

        // Check availability via provider
        $available = $this->provider->checkAvailability($domain);

        return new DomainResult(
            domain: $domain,
            available: $available,
            pricing: $pricing,
        );
    }
}
```

---

## Idempotency

### Payment Webhook
```php
$orderId = $payload['order_id'];

// Prevent double payment processing
$existing = Payment::where('transaction_id', $orderId)
    ->where('status', 'paid')
    ->exists();

if ($existing) {
    return response()->json(['status' => 'already_processed']);
}
```

### Domain Registration
```php
$existing = Domain::where('domain_name', $domain)->exists();
if ($existing) {
    throw new AlreadyRegisteredException("Domain {$domain} is already registered");
}
```

### VPS Provisioning
```php
$existing = ProvisioningJob::where('service_type', 'vps')
    ->where('service_id', $vpsServiceId)
    ->where('action', 'create')
    ->whereIn('status', ['queued', 'running'])
    ->exists();

if ($existing) {
    return; // Already queued or in progress
}
```

---

## Error Handling & Retry

### Retry Strategy

| Service | Max Attempts | Backoff | On Final Failure |
|---|---|---|---|
| VPS Provisioning | 3 | 30s, 60s, 120s | Mark service as 'failed', notify admin |
| Hosting Provisioning | 3 | 30s, 60s, 120s | Mark service as 'failed', notify admin |
| Domain Registration | 3 | 30s, 60s, 120s | Refund payment, notify customer + admin |
| Payment Verification | 3 | 10s, 30s, 60s | Flag for manual review |

### Error Mapping

```php
class ProxmoxApiClient
{
    private function handleApiError(Response $response): void
    {
        $status = $response->status();

        match ($status) {
            401 => throw new AuthenticationException('Proxmox authentication failed'),
            403 => throw new AuthorizationException('Proxmox permission denied'),
            404 => throw new ResourceNotFoundException('VM or node not found'),
            409 => throw new ResourceConflictException('Resource conflict'),
            500 => throw new ProvisioningFailedException(
                "Proxmox server error: {$response->body()}"
            ),
            503 => throw new ServiceUnavailableException('Proxmox API unavailable'),
            default => throw new ProvisioningFailedException(
                "Proxmox API error [{$status}]: {$response->body()}"
            ),
        };
    }
}
```

### Customer-Facing Errors

| Raw Error | Customer Message |
|---|---|
| cURL error 28 (timeout) | "Layanan sedang mengalami gangguan sementara. Silakan coba kembali." |
| Authentication failed | "Konfigurasi server sedang diperiksa. Tim kami akan segera menanganinya." |
| Insufficient resources | "Kapasitas server sedang penuh. Tim kami akan menambah kapasitas." |
| Domain registration failed | "Pendaftaran domain gagal. Tim kami akan menghubungi Anda." |

---

## Provisioning Queue Monitoring

Admin dashboard menampilkan:
- Active provisioning jobs (queued + running)
- Recent failures (last 24h)
- Retry queue status
- Per-server provisioning stats

### `provisioning:status` Artisan Command
```bash
php artisan provisioning:status

# Output:
# VPS Provisioning:
#   Queued: 3 | Running: 1 | Failed: 0
# Hosting Provisioning:
#   Queued: 0 | Running: 0 | Failed: 0
# Domain Registration:
#   Queued: 2 | Running: 0 | Failed: 1
```

### `provisioning:retry-failed` Artisan Command
```bash
php artisan provisioning:retry-failed {--service-type=vps} {--service-id=}
# Push failed provisioning jobs back to queue
```