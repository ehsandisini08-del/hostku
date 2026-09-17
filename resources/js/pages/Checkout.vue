<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2 } from '@lucide/vue';
import { ref, watch } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Checkout', href: '#' }] } });

const props = defineProps<{
    invoice: any;
    paymentResult?: any;
}>();

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const gateways = [
    {
        id: 'simulation',
        name: 'Instant Payment / Sandbox Simulator',
        methods: [
            { id: 'instant', name: 'Bayar Langsung (Testing / Sandbox)', channels: ['instant_approval'] },
            { id: 'bank_transfer', name: 'Transfer Bank Manual', channels: ['BCA 1234567890 (a/n HostKu)', 'Mandiri 0987654321'] },
        ],
    },
    { id: 'midtrans', name: 'Midtrans', methods: [{ id: 'bank_transfer', name: 'Bank Transfer', channels: ['bca', 'bni', 'mandiri', 'bri', 'permata'] }, { id: 'qris', name: 'QRIS', channels: [] }, { id: 'gopay', name: 'GoPay', channels: [] }] },
    { id: 'xendit', name: 'Xendit', methods: [{ id: 'virtual_account', name: 'Virtual Account', channels: ['BCA', 'BNI', 'BRI', 'Mandiri'] }, { id: 'ewallet', name: 'E-Wallet', channels: ['OVO', 'DANA', 'LinkAja'] }] },
    { id: 'doku', name: 'DOKU', methods: [{ id: 'bank_transfer', name: 'Bank Transfer', channels: ['bca', 'mandiri'] }] },
];

const selectedGateway = ref('simulation');
const selectedMethod = ref('instant');
const selectedChannel = ref('instant_approval');

watch(selectedGateway, (newGateway) => {
    const g = gateways.find((gw) => gw.id === newGateway);
    if (g && g.methods.length > 0) {
        selectedMethod.value = g.methods[0].id;
        selectedChannel.value = g.methods[0].channels[0] || '';
    }
});

watch(selectedMethod, (newMethod) => {
    const g = gateways.find((gw) => gw.id === selectedGateway.value);
    const m = g?.methods.find((me) => me.id === newMethod);
    selectedChannel.value = m?.channels[0] || '';
});

const isProcessing = ref(false);

function processPayment() {
    isProcessing.value = true;
    router.post(
        `/checkout/${props.invoice.id}/pay`,
        {
            gateway: selectedGateway.value,
            payment_method: selectedMethod.value,
            payment_channel: selectedChannel.value,
        },
        {
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Checkout" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Checkout</h1>
            <span
                v-if="invoice.status === 'paid'"
                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-950 dark:text-green-300"
            >
                <CheckCircle2 class="h-3.5 w-3.5" /> LUNAS (PAID)
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Invoice {{ invoice.invoice_number }}</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                                <th class="py-2 text-neutral-500">Item</th>
                                <th class="py-2 text-right text-neutral-500">Qty</th>
                                <th class="py-2 text-right text-neutral-500">Price</th>
                                <th class="py-2 text-right text-neutral-500">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in invoice.items" :key="item.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                                <td class="py-3 text-neutral-900 dark:text-white">{{ item.description }}</td>
                                <td class="py-3 text-right text-neutral-500">{{ item.quantity }}</td>
                                <td class="py-3 text-right text-neutral-500">{{ formatPrice(item.unit_price) }}</td>
                                <td class="py-3 text-right font-medium text-neutral-900 dark:text-white">{{ formatPrice(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="invoice.status !== 'paid' && !paymentResult" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Metode Pembayaran</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-neutral-500">Payment Gateway</label>
                            <select v-model="selectedGateway" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                <option v-for="g in gateways" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-neutral-500">Metode</label>
                            <select v-model="selectedMethod" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                <option v-for="m in gateways.find(g => g.id === selectedGateway)?.methods" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                        </div>
                        <div v-if="gateways.find(g => g.id === selectedGateway)?.methods.find(m => m.id === selectedMethod)?.channels.length">
                            <label class="mb-1 block text-xs font-medium text-neutral-500">Channel / Rekening</label>
                            <select v-model="selectedChannel" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                <option v-for="c in gateways.find(g => g.id === selectedGateway)?.methods.find(m => m.id === selectedMethod)?.channels" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Subtotal</span>
                        <span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.subtotal) }}</span>
                    </div>
                    <div v-if="invoice.discount_amount" class="flex justify-between">
                        <span class="text-green-600">Discount</span>
                        <span class="text-green-600">-{{ formatPrice(invoice.discount_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Tax</span>
                        <span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.tax_amount) }}</span>
                    </div>
                    <hr class="my-3 border-neutral-200 dark:border-neutral-800" />
                    <div class="flex justify-between text-lg font-bold">
                        <span class="text-neutral-900 dark:text-white">Total</span>
                        <span class="text-neutral-900 dark:text-white">{{ formatPrice(invoice.total) }}</span>
                    </div>
                </div>

                <div v-if="invoice.status === 'paid'" class="mt-6">
                    <Link href="/customer/hosting-services" class="block w-full rounded-lg bg-green-600 py-3 text-center text-sm font-medium text-white hover:bg-green-700">
                        Buka Layanan Hosting Saya <ArrowRight class="ml-1 inline h-4 w-4" />
                    </Link>
                </div>

                <div v-else-if="paymentResult?.redirectUrl" class="mt-6">
                    <a :href="paymentResult.redirectUrl" class="block w-full rounded-lg bg-blue-600 py-3 text-center text-sm font-medium text-white hover:bg-blue-700">
                        Pay Now <ArrowRight class="ml-1 inline h-4 w-4" />
                    </a>
                </div>

                <div v-else-if="paymentResult?.vaNumber" class="mt-6 space-y-3">
                    <div class="rounded-lg bg-neutral-50 p-4 text-center dark:bg-neutral-900">
                        <p class="text-xs text-neutral-500">Virtual Account Number</p>
                        <p class="mt-1 text-lg font-bold text-neutral-900 dark:text-white">{{ paymentResult.vaNumber }}</p>
                    </div>
                    <button @click="processPayment" :disabled="isProcessing" class="w-full rounded-lg bg-blue-600 py-3 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Konfirmasi Pembayaran
                    </button>
                </div>

                <div v-else class="mt-6">
                    <button
                        @click="processPayment"
                        :disabled="isProcessing"
                        class="w-full rounded-lg bg-blue-600 py-3 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isProcessing ? 'Memproses...' : `Bayar Sekarang (${formatPrice(invoice.total)})` }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>