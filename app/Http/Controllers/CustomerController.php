<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function dashboard(): Response
    {
        $user = Auth::user();

        return Inertia::render('customer/Dashboard', [
            'stats' => [
                'totalServices' => Service::where('user_id', $user->id)->count(),
                'activeServices' => Service::where('user_id', $user->id)->where('status', 'active')->count(),
                'pendingOrders' => Order::where('user_id', $user->id)->whereIn('status', ['pending', 'awaiting_payment'])->count(),
                'unpaidInvoices' => Invoice::where('user_id', $user->id)->where('status', 'pending')->count(),
                'openTickets' => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            ],
            'recentOrders' => Order::with('items.product')
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'recentInvoices' => Invoice::where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function services(): Response
    {
        $services = Service::with('order')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/Services', [
            'services' => $services,
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/Orders', [
            'orders' => $orders,
        ]);
    }

    public function showOrder(Order $order): Response
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'invoice.payment']);

        return Inertia::render('customer/OrderDetail', [
            'order' => $order,
        ]);
    }

    public function invoices(): Response
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/Invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function showInvoice(Invoice $invoice): Response
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->load(['items', 'payment', 'order.items.product']);

        return Inertia::render('customer/InvoiceDetail', [
            'invoice' => $invoice,
        ]);
    }

    public function payments(): Response
    {
        $payments = Payment::whereHas('invoice', function ($q) {
            $q->where('user_id', Auth::id());
        })->with('invoice')->latest()->paginate(20);

        return Inertia::render('customer/Payments', [
            'payments' => $payments,
        ]);
    }

    public function tickets(): Response
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return Inertia::render('customer/Tickets', [
            'tickets' => $tickets,
        ]);
    }

    public function createTicket(): Response
    {
        $services = Service::where('user_id', Auth::id())
            ->where('status', 'active')
            ->get();

        return Inertia::render('customer/TicketCreate', [
            'services' => $services,
        ]);
    }

    public function storeTicket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'service_id' => $validated['service_id'] ?? null,
        ]);

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        return redirect()->route('customer.tickets')->with('success', 'Ticket berhasil dibuat.');
    }

    public function showTicket(Ticket $ticket): Response
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load(['messages.user', 'service']);

        return Inertia::render('customer/TicketDetail', [
            'ticket' => $ticket,
        ]);
    }

    public function replyTicket(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $ticket->update(['last_reply_at' => now(), 'status' => 'open']);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function notifications(): Response
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return Inertia::render('customer/Notifications', [
            'notifications' => $notifications,
        ]);
    }
}
