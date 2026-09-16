<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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

        $hosting->load(['plan.product', 'server', 'service']);

        return Inertia::render('customer/HostingDetail', [
            'hosting' => $hosting,
        ]);
    }

    public function changePassword(Request $request, HostingService $hosting): RedirectResponse
    {
        if ($hosting->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);

        $this->provisioning->changePassword($hosting, $request->input('password'));

        return back()->with('success', 'Password changed successfully.');
    }
}
