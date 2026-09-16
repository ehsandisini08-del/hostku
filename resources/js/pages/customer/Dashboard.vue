<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Server, ShoppingBag, FileText, MessageSquare, Clock } from '@lucide/vue';


defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }] },
});

const props = defineProps<{
    stats: {
        totalServices: number;
        activeServices: number;
        pendingOrders: number;
        unpaidInvoices: number;
        openTickets: number;
    };
    recentOrders: any[];
    recentInvoices: any[];
}>();

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
const statusColor = (s: string) => {
    const m: Record<string, string> = { pending: 'bg-yellow-100 text-yellow-800', active: 'bg-green-100 text-green-800', paid: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800', suspended: 'bg-red-100 text-red-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"><Server class="h-5 w-5 text-blue-600 dark:text-blue-400" /></div>
                <div><p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.activeServices }}</p><p class="text-xs text-neutral-500">Active Services</p></div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/30"><ShoppingBag class="h-5 w-5 text-yellow-600 dark:text-yellow-400" /></div>
                <div><p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.pendingOrders }}</p><p class="text-xs text-neutral-500">Pending Orders</p></div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30"><FileText class="h-5 w-5 text-red-600 dark:text-red-400" /></div>
                <div><p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.unpaidInvoices }}</p><p class="text-xs text-neutral-500">Unpaid Invoices</p></div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30"><MessageSquare class="h-5 w-5 text-purple-600 dark:text-purple-400" /></div>
                <div><p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.openTickets }}</p><p class="text-xs text-neutral-500">Open Tickets</p></div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30"><Server class="h-5 w-5 text-green-600 dark:text-green-400" /></div>
                <div><p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.totalServices }}</p><p class="text-xs text-neutral-500">Total Services</p></div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Recent Orders</h3>
                <div v-if="recentOrders.length === 0" class="py-8 text-center text-sm text-neutral-500">Belum ada order.</div>
                <div v-else class="space-y-3">
                    <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between border-b border-neutral-100 pb-3 last:border-0 dark:border-neutral-800">
                        <div>
                            <Link :href="`/customer/orders/${order.id}`" class="font-medium text-neutral-900 hover:text-blue-600 dark:text-white">{{ order.order_number }}</Link>
                            <p class="text-xs text-neutral-500">{{ order.items[0]?.description ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(order.status)]">{{ order.status }}</span>
                            <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ formatPrice(order.total) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Recent Invoices</h3>
                <div v-if="recentInvoices.length === 0" class="py-8 text-center text-sm text-neutral-500">Belum ada invoice.</div>
                <div v-else class="space-y-3">
                    <div v-for="inv in recentInvoices" :key="inv.id" class="flex items-center justify-between border-b border-neutral-100 pb-3 last:border-0 dark:border-neutral-800">
                        <div>
                            <Link :href="`/customer/invoices/${inv.id}`" class="font-medium text-neutral-900 hover:text-blue-600 dark:text-white">{{ inv.invoice_number }}</Link>
                            <p class="text-xs text-neutral-500">Due: {{ inv.due_date }}</p>
                        </div>
                        <div class="text-right">
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(inv.status)]">{{ inv.status }}</span>
                            <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ formatPrice(inv.total) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>