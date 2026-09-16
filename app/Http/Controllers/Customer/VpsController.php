<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\VpsService;
use App\Services\Proxmox\ProxmoxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VpsController extends Controller
{
    public function __construct(private ProxmoxService $proxmox) {}

    public function index(): Response
    {
        $services = VpsService::with(['plan.product', 'node.server'])
            ->whereHas('service', fn ($q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/VpsServices', [
            'services' => $services,
        ]);
    }

    public function show(VpsService $vps): Response
    {
        if ($vps->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $vps->load(['plan.product', 'node.server', 'service']);
        $status = $vps->node ? $this->proxmox->getVmStatus($vps) : [];

        return Inertia::render('customer/VpsDetail', [
            'vps' => $vps,
            'vmStatus' => $status,
        ]);
    }

    public function start(VpsService $vps): RedirectResponse
    {
        if ($vps->service?->user_id !== Auth::id()) {
            abort(403);
        }
        $this->proxmox->startVm($vps);

        return back()->with('success', 'VPS started.');
    }

    public function stop(VpsService $vps): RedirectResponse
    {
        if ($vps->service?->user_id !== Auth::id()) {
            abort(403);
        }
        $this->proxmox->stopVm($vps);

        return back()->with('success', 'VPS stopped.');
    }

    public function reboot(VpsService $vps): RedirectResponse
    {
        if ($vps->service?->user_id !== Auth::id()) {
            abort(403);
        }
        $this->proxmox->rebootVm($vps);

        return back()->with('success', 'VPS rebooted.');
    }

    public function shutdown(VpsService $vps): RedirectResponse
    {
        if ($vps->service?->user_id !== Auth::id()) {
            abort(403);
        }
        $this->proxmox->shutdownVm($vps);

        return back()->with('success', 'VPS shutdown initiated.');
    }
}
