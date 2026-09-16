<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Invoices', href: '/customer/invoices' }] } });

const props = defineProps<{ invoices: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Invoices" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">My Invoices</h1>

        <div v-if="invoices.data.length === 0" class="py-16 text-center">
            <FileText class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Belum ada invoice.</p>
        </div>

        <div v-else class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Invoice</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Due Date</th>
                        <th class="px-4 py-3 font-semibold text-neutral-900 dark:text-white">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-neutral-900 dark:text-white">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices.data" :key="inv.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3">
                            <Link :href="`/customer/invoices/${inv.id}`" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">{{ inv.invoice_number }}</Link>
                        </td>
                        <td class="px-4 py-3 text-neutral-500">{{ inv.due_date }}</td>
                        <td class="px-4 py-3">
                            <span :class="inv.status === 'paid' ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800' : 'rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800'">{{ inv.status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(inv.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>