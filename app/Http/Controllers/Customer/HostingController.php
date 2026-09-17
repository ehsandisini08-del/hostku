<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HostingEmail;
use App\Models\HostingService;
use App\Services\Hosting\HostingProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HostingController extends Controller
{
    public function __construct(private HostingProvisioningService $provisioning) {}

    public function index(): Response
    {
        $services = HostingService::with(['plan.product', 'server'])
            ->whereHas('service', fn ($q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/HostingServices', [
            'services' => $services,
        ]);
    }

    public function show(HostingService $hosting): Response
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $hosting->load(['plan.product', 'server', 'service', 'emails']);

        $dnsCheck = $this->provisioning->checkDns($hosting->domain ?? '', $hosting->server_ip ?? '');

        return Inertia::render('customer/HostingDetail', [
            'hosting' => $hosting,
            'dnsCheck' => $dnsCheck,
            'webmailUrl' => 'http://'.($hosting->server_ip ?: '127.0.0.1').'/webmail',
            'phpMyAdminUrl' => 'http://'.($hosting->server_ip ?: '127.0.0.1').'/phpmyadmin',
        ]);
    }

    public function changePassword(Request $request, HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);

        $this->provisioning->changePassword($hosting, $request->input('password'));

        return back()->with('success', 'Password SFTP berhasil diperbarui.');
    }

    public function reissueSsl(HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $result = $this->provisioning->reissueSsl($hosting);

        if ($result['success'] ?? false) {
            return back()->with('success', 'Sertifikat SSL Let\'s Encrypt berhasil diterbitkan dan aktif!');
        }

        return back()->with('error', 'Gagal menerbitkan SSL. Pastikan domain telah mengarah (A record) ke IP '.$hosting->server_ip);
    }

    public function changePhpVersion(Request $request, HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'php_version' => ['required', 'in:7.4,8.0,8.1,8.2,8.3,8.4'],
        ]);

        $this->provisioning->changePhpVersion($hosting, $validated['php_version']);

        return back()->with('success', 'Versi PHP berhasil diubah ke PHP '.$validated['php_version']);
    }

    public function resetDatabasePassword(Request $request, HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->provisioning->resetDatabasePassword($hosting, $validated['password']);

        return back()->with('success', 'Password database MySQL berhasil diperbarui.');
    }

    public function storeEmail(Request $request, HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'password' => ['required', 'string', 'min:8'],
            'quota_mb' => ['nullable', 'integer', 'min:50', 'max:5000'],
        ]);

        try {
            $this->provisioning->createMailbox(
                $hosting,
                strtolower($validated['username']),
                $validated['password'],
                $validated['quota_mb'] ?? 500,
            );

            return back()->with('success', 'Akun email '.$validated['username'].'@'.$hosting->domain.' berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyEmail(HostingService $hosting, HostingEmail $email): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id() || $email->hosting_service_id !== $hosting->id) {
            abort(403);
        }

        $this->provisioning->deleteMailbox($hosting, $email);

        return back()->with('success', 'Akun email berhasil dihapus.');
    }

    public function changeEmailPassword(Request $request, HostingService $hosting, HostingEmail $email): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id() || $email->hosting_service_id !== $hosting->id) {
            abort(403);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $this->provisioning->changeMailboxPassword($hosting, $email, $validated['password']);

        return back()->with('success', 'Password akun email berhasil diubah.');
    }
}
