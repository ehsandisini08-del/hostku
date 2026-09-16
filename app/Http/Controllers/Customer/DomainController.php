<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\Domain\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DomainController extends Controller
{
    public function __construct(private DomainService $domainService) {}

    public function index(): Response
    {
        $domains = Domain::whereHas('service', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('service')
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/Domains', [
            'domains' => $domains,
        ]);
    }

    public function show(Domain $domain): Response
    {
        if ($domain->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $domain->load(['contacts', 'registrationLogs', 'service']);

        return Inertia::render('customer/DomainDetail', [
            'domain' => $domain,
        ]);
    }

    public function updateNameservers(Request $request, Domain $domain): RedirectResponse
    {
        if ($domain->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'ns1' => ['required', 'string', 'max:255'],
            'ns2' => ['required', 'string', 'max:255'],
            'ns3' => ['nullable', 'string', 'max:255'],
            'ns4' => ['nullable', 'string', 'max:255'],
        ]);

        $nameservers = array_values(array_filter([$validated['ns1'], $validated['ns2'], $validated['ns3'] ?? null, $validated['ns4'] ?? null]));

        $this->domainService->updateNameservers($domain, $nameservers);

        return back()->with('success', 'Nameservers updated.');
    }

    public function renew(Request $request, Domain $domain): RedirectResponse
    {
        if ($domain->service?->user_id !== Auth::id()) {
            abort(403);
        }

        $years = (int) $request->input('years', 1);
        $this->domainService->renew($domain, $years);

        return back()->with('success', 'Domain renewed for '.$years.' year(s).');
    }
}
