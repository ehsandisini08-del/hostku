<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CreditCard } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Payments', href: '/customer/payments' }] } });

const props = defineProps<{ payments: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Payments" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Payment History</h1>

        <div v-if="payments.data.length === 0" class="py-16 text-center">
            <CreditCard class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Belum ada pembayaran.</p>
        </div>

        <div v-else class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Transaction</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Gateway</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Method</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-neutral-900 dark:text-white">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payments.data" :key="p.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3 text-neutral-900 dark:text-white">{{ p.transaction_id }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ p.gateway }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ p.payment_channel ?? 'N/A' }}</td>
                        <td class="px-4 py-3"><span :class="p.status === 'paid' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800' : 'rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-800'">{{ p.status }}</span></td>
                        <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(p.amount) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>