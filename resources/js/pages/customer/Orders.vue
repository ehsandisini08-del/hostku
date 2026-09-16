<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Orders', href: '/customer/orders' }] } });

const props = defineProps<{ orders: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
const statusColor = (s: string) => {
    const m: Record<string, string> = { active: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', paid: 'bg-green-100 text-green-800', failed: 'bg-red-100 text-red-800', cancelled: 'bg-neutral-100 text-neutral-800', processing: 'bg-blue-100 text-blue-800', provisioning: 'bg-purple-100 text-purple-800', awaiting_payment: 'bg-orange-100 text-orange-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
</script>

<template>
    <Head title="Orders" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">My Orders</h1>

        <div v-if="orders.data.length === 0" class="py-16 text-center">
            <ShoppingBag class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Belum ada order.</p>
            <Link href="/hosting" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Mulai berlangganan</Link>
        </div>

        <div v-else class="space-y-3">
            <div v-for="order in orders.data" :key="order.id" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex items-center justify-between">
                    <div>
                        <Link :href="`/customer/orders/${order.id}`" class="font-semibold text-neutral-900 hover:text-blue-600 dark:text-white">{{ order.order_number }}</Link>
                        <p class="text-xs text-neutral-500">{{ order.items?.[0]?.description ?? 'N/A' }} • {{ new Date(order.created_at).toLocaleDateString() }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(order.status)]">{{ order.status }}</span>
                        <p class="mt-1 font-semibold text-neutral-900 dark:text-white">{{ formatPrice(order.total) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>