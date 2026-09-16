<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CreditCard, Banknote, QrCode, ArrowRight } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Checkout', href: '#' }] } });

const props = defineProps<{
    invoice: any;
    paymentResult?: any;
}>();

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const selectedGateway = ref('midtrans');
const selectedMethod = ref('bank_transfer');
const selectedChannel = ref('bca');

const gateways = [
    { id: 'midtrans', name: 'Midtrans', methods: [{ id: 'bank_transfer', name: 'Bank Transfer', channels: ['bca', 'bni', 'mandiri', 'bri', 'permata'] }, { id: 'qris', name: 'QRIS', channels: [] }, { id: 'gopay', name: 'GoPay', channels: [] }] },
    { id: 'xendit', name: 'Xendit', methods: [{ id: 'virtual_account', name: 'Virtual Account', channels: ['BCA', 'BNI', 'BRI', 'Mandiri'] }, { id: 'ewallet', name: 'E-Wallet', channels: ['OVO', 'DANA', 'LinkAja'] }] },
    { id: 'doku', name: 'DOku', methods: [{ id: 'bank_transfer', name: 'Bank Transfer', channels: ['bca', 'mandiri'] }] },
];
</script>

<template>
    <Head title="Checkout" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Checkout</h1>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Invoice {{ invoice.invoice_number }}</h3>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="py-2 text-neutral-500">Item</th><th class="py-2 text-right text-neutral-500">Qty</th><th class="py-2 text-right text-neutral-500">Price</th><th class="py-2 text-right text-neutral-500">Total</th></tr></thead>
                        <tbody><tr v-for="item in invoice.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td><td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td><td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td><td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td></tr></tbody>
                    </table>
                </div>

                <div v-if="!paymentResult" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Payment Method</h3>
                    <div class="space-y-3">
                        <select v-model="selectedGateway" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                            <option v-for="g in gateways" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                        <select v-model="selectedMethod" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                            <option v-for="m in gateways.find(g => g.id === selectedGateway)?.methods" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                        <select v-if="gateways.find(g => g.id === selectedGateway)?.methods.find(m => m.id === selectedMethod)?.channels.length" v-model="selectedChannel" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                            <option v-for="c in gateways.find(g => g.id === selectedGateway)?.methods.find(m => m.id === selectedMethod)?.channels" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Summary</h3>
                <div class="space-y-1 text-sm"><div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.subtotal) }}</span></div><div v-if="invoice.discount_amount" class="flex justify-between"><span class="text-green-600">Discount</span><span class="text-green-600">-{{ formatPrice(invoice.discount_amount) }}</span></div><div class="flex justify-between"><span class="text-neutral-500">Tax</span><span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.tax_amount) }}</span></div><hr class="my-2 border-neutral-200 dark:border-neutral-800" /><div class="flex justify-between text-lg font-bold"><span class="text-neutral-900 dark:text-white">Total</span><span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.total) }}</span></div></div>

                <div v-if="paymentResult?.redirectUrl" class="mt-4">
                    <a :href="paymentResult.redirectUrl" class="block w-full rounded-lg bg-blue-600 py-3 text-center text-sm font-medium text-white hover:bg-blue-700">Pay Now <ArrowRight class="ml-1 inline h-4 w-4" /></a>
                </div>
                <div v-if="paymentResult?.vaNumber" class="mt-4 rounded-lg bg-neutral-50 p-3 text-center dark:bg-neutral-900">
                    <p class="text-xs text-neutral-500">Virtual Account Number</p>
                    <p class="text-lg font-bold text-neutral-900 dark:text-white">{{ paymentResult.vaNumber }}</p>
                </div>
                <button v-if="!paymentResult" @click="router.post(`/checkout/${invoice.id}/pay`, { gateway: selectedGateway, payment_method: selectedMethod, payment_channel: selectedChannel })" class="mt-4 w-full rounded-lg bg-blue-600 py-3 text-sm font-medium text-white hover:bg-blue-700">Pay {{ formatPrice(invoice.total) }}</button>
            </div>
        </div>
    </div>
</template>