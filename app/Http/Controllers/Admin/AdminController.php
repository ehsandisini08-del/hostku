<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\Domain;
use App\Models\DomainPricing;
use App\Models\HostingPlan;
use App\Models\HostingServer;
use App\Models\HostingService;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProxmoxServer;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\VpsPlan;
use App\Models\VpsService;
use App\Services\Proxmox\ProxmoxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'revenue' => [
                'total' => Payment::where('status', 'paid')->sum('amount'),
                'this_month' => Payment::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount'),
            ],
            'counts' => [
                'orders' => Order::count(),
                'active_customers' => User::whereHas('role', fn ($q) => $q->where('slug', 'customer'))->count(),
                'active_services' => Service::where('status', 'active')->count(),
                'domains' => DomainPricing::count(),
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'failed_payments' => Payment::where('status', 'failed')->count(),
                'open_tickets' => Ticket::where('status', 'open')->count(),
            ],
            'recentOrders' => Order::with('user')->latest()->limit(10)->get(),
        ]);
    }

    public function customers(): Response
    {
        return Inertia::render('admin/Customers', [
            'customers' => User::with('role')
                ->whereHas('role', fn ($q) => $q->where('slug', 'customer'))
                ->latest()
                ->paginate(20),
        ]);
    }

    public function showCustomer(User $user): Response
    {
        $user->load(['role', 'orders' => fn ($q) => $q->latest()->limit(10), 'services' => fn ($q) => $q->latest()->limit(10)]);

        return Inertia::render('admin/CustomerDetail', [
            'customer' => $user,
            'stats' => [
                'total_orders' => $user->orders()->count(),
                'total_services' => $user->services()->count(),
                'total_invoices' => Invoice::where('user_id', $user->id)->count(),
                'total_spent' => Payment::whereHas('invoice', fn ($q) => $q->where('user_id', $user->id))->where('status', 'paid')->sum('amount'),
            ],
        ]);
    }

    public function products(): Response
    {
        return Inertia::render('admin/Products', [
            'products' => Product::with(['domainPricing', 'hostingPlan', 'vpsPlan', 'prices'])->latest()->paginate(20),
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:domain,hosting,vps'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $product = Product::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'features' => $validated['features'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($validated['type'] === 'domain') {
            DomainPricing::create($request->validate([
                'tld' => ['required', 'string', 'max:20'],
                'registration_price' => ['required', 'numeric'],
                'renewal_price' => ['required', 'numeric'],
                'transfer_price' => ['required', 'numeric'],
            ]) + ['product_id' => $product->id]);
        } elseif ($validated['type'] === 'hosting') {
            HostingPlan::create($request->validate([
                'disk_space_mb' => ['required', 'integer'],
                'max_websites' => ['required', 'integer'],
                'max_databases' => ['required', 'integer'],
                'max_emails' => ['required', 'integer'],
            ]) + ['product_id' => $product->id]);
        } elseif ($validated['type'] === 'vps') {
            VpsPlan::create($request->validate([
                'cpu_cores' => ['required', 'integer'],
                'ram_mb' => ['required', 'integer'],
                'disk_mb' => ['required', 'integer'],
            ]) + ['product_id' => $product->id]);
        }

        return back()->with('success', 'Produk berhasil dibuat.');
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $product->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('success', 'Produk berhasil diupdate.');
    }

    public function orders(): Response
    {
        return Inertia::render('admin/Orders', [
            'orders' => Order::with(['user', 'items.product'])->latest()->paginate(20),
        ]);
    }

    public function showOrder(Order $order): Response
    {
        $order->load(['user', 'items.product', 'invoice.payment']);

        return Inertia::render('admin/OrderDetail', [
            'order' => $order,
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('admin/Services', [
            'services' => Service::with('user')->latest()->paginate(20),
        ]);
    }

    public function invoices(): Response
    {
        return Inertia::render('admin/Invoices', [
            'invoices' => Invoice::with('user')->latest()->paginate(20),
        ]);
    }

    public function showInvoice(Invoice $invoice): Response
    {
        $invoice->load(['user', 'items', 'payment']);

        return Inertia::render('admin/InvoiceDetail', [
            'invoice' => $invoice,
        ]);
    }

    public function payments(): Response
    {
        return Inertia::render('admin/Payments', [
            'payments' => Payment::with(['invoice.user'])->latest()->paginate(20),
        ]);
    }

    public function tickets(): Response
    {
        return Inertia::render('admin/Tickets', [
            'tickets' => Ticket::with('user')->latest()->paginate(20),
        ]);
    }

    public function showTicket(Ticket $ticket): Response
    {
        $ticket->load(['user', 'messages.user']);

        return Inertia::render('admin/TicketDetail', [
            'ticket' => $ticket,
        ]);
    }

    public function replyTicket(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate(['message' => ['required', 'string']]);
        $ticket->messages()->create(['user_id' => auth()->id(), 'message' => $validated['message']]);
        $ticket->update(['last_reply_at' => now(), 'status' => 'answered']);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function coupons(): Response
    {
        return Inertia::render('admin/Coupons', [
            'coupons' => Coupon::latest()->paginate(20),
        ]);
    }

    public function storeCoupon(Request $request): RedirectResponse
    {
        Coupon::create($request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric'],
            'max_usage' => ['nullable', 'integer'],
            'per_user_limit' => ['nullable', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]));

        return back()->with('success', 'Kupon berhasil dibuat.');
    }

    public function settings(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function adminUsers(): Response
    {
        return Inertia::render('admin/Users', [
            'users' => User::with('role')
                ->whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'super_admin']))
                ->paginate(20),
            'roles' => Role::whereIn('slug', ['admin', 'super_admin'])->get(),
        ]);
    }

    public function auditLogs(): Response
    {
        return Inertia::render('admin/AuditLogs', [
            'logs' => AuditLog::with('user')->latest()->paginate(50),
        ]);
    }

    public function domains(): Response
    {
        return Inertia::render('admin/Domains', [
            'domains' => Domain::with('service.user')->latest()->paginate(20),
        ]);
    }

    public function showDomain(Domain $domain): Response
    {
        $domain->load(['contacts', 'registrationLogs', 'service.user']);

        return Inertia::render('admin/DomainDetail', [
            'domain' => $domain,
        ]);
    }

    public function hosting(): Response
    {
        $services = HostingService::with(['service.user', 'server', 'plan.product'])->latest()->paginate(20);

        return Inertia::render('admin/Hosting', [
            'services' => $services,
        ]);
    }

    public function hostingServers(): Response
    {
        return Inertia::render('admin/HostingServers', [
            'servers' => HostingServer::latest()->paginate(20),
        ]);
    }

    public function storeHostingServer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hostname' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:45'],
            'panel_type' => ['required', 'in:cpanel,directadmin,custom_ssh'],
            'api_url' => ['nullable', 'string', 'max:255'],
            'api_token' => ['nullable', 'string'],
            'api_username' => ['nullable', 'string', 'max:255'],
            'ssh_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_user' => ['nullable', 'string', 'max:100'],
            'ssh_key_path' => ['nullable', 'string', 'max:255'],
            'web_server' => ['nullable', 'string', 'max:50'],
            'php_version' => ['nullable', 'string', 'max:20'],
            'base_path' => ['nullable', 'string', 'max:255'],
            'ssl_email' => ['nullable', 'email', 'max:255'],
        ]);

        HostingServer::create($validated);

        return back()->with('success', 'Server added.');
    }

    public function vps(): Response
    {
        return Inertia::render('admin/Vps', [
            'services' => VpsService::with(['service.user', 'plan.product', 'node'])->latest()->paginate(20),
        ]);
    }

    public function proxmox(): Response
    {
        return Inertia::render('admin/Proxmox', [
            'servers' => ProxmoxServer::with('nodes')->latest()->paginate(20),
        ]);
    }

    public function storeProxmoxServer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer'],
            'auth_type' => ['required', 'in:api_token'],
            'token_id' => ['required', 'string'],
            'token_secret' => ['required', 'string'],
        ]);

        ProxmoxServer::create($validated);

        return back()->with('success', 'Proxmox server added.');
    }

    public function testProxmoxConnection(ProxmoxServer $server, ProxmoxService $proxmox): JsonResponse
    {
        $success = $proxmox->testConnection($server);
        $server->refresh();

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Connection successful.' : 'Connection failed.',
            'last_checked_at' => $server->last_checked_at?->toDateTimeString(),
            'is_active' => $server->is_active,
        ]);
    }

    public function syncProxmoxNodes(ProxmoxServer $server, ProxmoxService $proxmox): JsonResponse
    {
        try {
            $nodes = $proxmox->syncNodes($server);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => count($nodes).' node(s) synced.',
            'nodes' => $server->fresh()->load('nodes')->nodes,
        ]);
    }

    public function showProxmoxServer(ProxmoxServer $server): JsonResponse
    {
        return response()->json([
            'server' => $server->load('nodes'),
        ]);
    }
}
