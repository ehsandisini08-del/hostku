<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, Check, Globe, ShieldCheck, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ plans: any[] }>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);

const aMonth = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'monthly');
const aYear = (product: any) => product.prices?.find((p: any) => p.billing_cycle === 'annually');

const selectedPlan = ref<any>(null);
const showOrderModal = ref(false);

const orderForm = useForm({
    product_id: null as number | null,
    billing_cycle: 'monthly',
    domain: '',
    auth_action: 'login' as 'login' | 'register',
});

function selectPlan(plan: any) {
    selectedPlan.value = plan;
    orderForm.product_id = plan.id;
    orderForm.billing_cycle = 'monthly';
    orderForm.domain = '';
    orderForm.auth_action = 'login';
    orderForm.clearErrors();
    showOrderModal.value = true;
}

const currentPrice = computed(() => {
    if (!selectedPlan.value) return 0;
    if (orderForm.billing_cycle === 'annually') {
        return aYear(selectedPlan.value)?.price ?? 0;
    }
    return aMonth(selectedPlan.value)?.price ?? 0;
});

function submitOrder(action: 'login' | 'register' = 'login') {
    orderForm.auth_action = action;
    orderForm.post('/order/hosting', {
        onSuccess: () => {
            showOrderModal.value = false;
        },
    });
}
</script>

<template>
    <Head title="Web Hosting — Cepat, Aman, Murah" />

    <section class="bg-gradient-to-b from-blue-50 to-white py-16 dark:from-blue-950 dark:to-neutral-950">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Web Hosting Indonesia</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Hosting cepat dengan SSD NVMe, SSL gratis, dan Nginx + PHP-FPM untuk performa maksimal</p>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="flex flex-col rounded-2xl border-2 p-8 transition-shadow hover:shadow-lg"
                    :class="plan.sort_order === 1 ? 'border-blue-600 shadow-md dark:border-blue-400' : 'border-neutral-200 dark:border-neutral-800'"
                >
                    <div v-if="plan.sort_order === 1" class="mb-4">
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Paling Populer</span>
                    </div>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">{{ plan.description }}</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-neutral-900 dark:text-white">{{ formatPrice(aMonth(plan)?.price ?? 0) }}</span>
                        <span class="text-neutral-500 dark:text-neutral-400">/bulan</span>
                    </div>
                    <p v-if="aYear(plan)" class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        {{ formatPrice(aYear(plan).price) }}/tahun (hemat)
                    </p>

                    <ul class="mt-8 flex-1 space-y-3">
                        <li v-for="(feat, i) in plan.features" :key="i" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <Check class="h-3 w-3" />
                            </span>
                            {{ feat }}
                        </li>
                    </ul>

                    <button
                        @click="selectPlan(plan)"
                        class="mt-8 block w-full rounded-lg bg-blue-600 py-3 text-center font-medium text-white transition hover:bg-blue-700"
                    >
                        Pilih Paket
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Konfigurasi Order Hosting -->
    <div v-if="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
        <div class="w-full max-w-lg rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b border-neutral-100 pb-4 dark:border-neutral-800">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Konfigurasi Hosting</h3>
                    <p class="text-xs text-neutral-500">Paket: {{ selectedPlan?.name }}</p>
                </div>
                <button @click="showOrderModal = false" class="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitOrder" class="mt-4 space-y-4">
                <div v-if="Object.keys(orderForm.errors).length > 0" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/50 dark:text-red-300">
                    <ul class="list-disc pl-5">
                        <li v-for="(err, key) in orderForm.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Nama Domain Anda</label>
                    <div class="relative mt-1">
                        <Globe class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                        <input
                            v-model="orderForm.domain"
                            type="text"
                            required
                            placeholder="contoh: bisnisku.com"
                            class="w-full rounded-lg border border-neutral-300 pl-9 pr-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        />
                    </div>
                    <p class="mt-1 text-xs text-neutral-500">Gunakan domain yang sudah Anda miliki atau yang akan didaftarkan.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Pilih Siklus Pembayaran</label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <label
                            class="flex cursor-pointer flex-col rounded-xl border p-3 text-sm transition"
                            :class="orderForm.billing_cycle === 'monthly' ? 'border-blue-600 bg-blue-50/50 dark:border-blue-500 dark:bg-blue-950/20' : 'border-neutral-200 dark:border-neutral-700'"
                        >
                            <input type="radio" v-model="orderForm.billing_cycle" value="monthly" class="sr-only" />
                            <span class="font-medium text-neutral-900 dark:text-white">Bulanan</span>
                            <span class="mt-1 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                {{ formatPrice(aMonth(selectedPlan)?.price ?? 0) }}/bln
                            </span>
                        </label>

                        <label
                            v-if="aYear(selectedPlan)"
                            class="flex cursor-pointer flex-col rounded-xl border p-3 text-sm transition"
                            :class="orderForm.billing_cycle === 'annually' ? 'border-blue-600 bg-blue-50/50 dark:border-blue-500 dark:bg-blue-950/20' : 'border-neutral-200 dark:border-neutral-700'"
                        >
                            <input type="radio" v-model="orderForm.billing_cycle" value="annually" class="sr-only" />
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-neutral-900 dark:text-white">Tahunan</span>
                                <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-bold text-green-700 dark:bg-green-950 dark:text-green-300">Hemat</span>
                            </div>
                            <span class="mt-1 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                {{ formatPrice(aYear(selectedPlan)?.price ?? 0) }}/thn
                            </span>
                        </label>
                    </div>
                </div>

                <div class="rounded-xl bg-neutral-50 p-4 text-sm dark:bg-neutral-800/50">
                    <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                        <span>Paket Hosting ({{ selectedPlan?.name }})</span>
                        <span class="font-medium text-neutral-900 dark:text-white">{{ formatPrice(currentPrice) }}</span>
                    </div>
                    <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                        <span>Setup Fee</span>
                        <span class="font-medium text-neutral-900 dark:text-white">Gratis</span>
                    </div>
                    <hr class="my-2 border-neutral-200 dark:border-neutral-700" />
                    <div class="flex justify-between text-base font-bold text-neutral-900 dark:text-white">
                        <span>Total Pembayaran</span>
                        <span class="text-blue-600 dark:text-blue-400">{{ formatPrice(currentPrice) }}</span>
                    </div>
                </div>

                <div v-if="user" class="pt-2">
                    <button
                        type="button"
                        @click="submitOrder('login')"
                        :disabled="orderForm.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ orderForm.processing ? 'Membuat Pesanan...' : 'Lanjut ke Pembayaran' }}
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </div>

                <div v-else class="space-y-2.5 pt-2">
                    <button
                        type="button"
                        @click="submitOrder('login')"
                        :disabled="orderForm.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ orderForm.processing ? 'Menyimpan Pesanan...' : 'Login & Lanjut ke Pembayaran' }}
                        <ArrowRight class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="submitOrder('register')"
                        :disabled="orderForm.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-blue-600 py-2.5 text-sm font-medium text-blue-600 transition hover:bg-blue-50 dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-950 disabled:opacity-50"
                    >
                        Daftar Akun Baru & Lanjut Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>