<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Search, Check, X, Shield, Globe } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: PublicLayout });

const props = defineProps<{ tlds: any[] }>();

const query = ref('');
const searchResults = ref<any[]>([]);
const searching = ref(false);

const formatPrice = (price: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);

function search() {
    if (!query.value.trim()) return;
    searching.value = true;
    setTimeout(() => {
        searchResults.value = props.tlds.map((t: any) => ({
            ...t,
            available: t.tld !== '.com' || query.value !== 'google',
        }));
        searching.value = false;
    }, 500);
}
</script>

<template>
    <Head title="Domain — Cari & Daftarkan Domain Murah" />

    <section class="bg-gradient-to-b from-blue-50 to-white py-16 dark:from-blue-950 dark:to-neutral-950">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Cari Domain Impian Anda</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Dapatkan domain murah dengan ekstensi .com, .id, .net, dan lainnya</p>
            <form class="mt-8 flex gap-2" @submit.prevent="search">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-neutral-400" />
                    <input v-model="query" type="text" placeholder="Cari domain..." class="w-full rounded-lg border border-neutral-300 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-8 py-3 text-sm font-medium text-white hover:bg-blue-700">Cari</button>
            </form>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <template v-if="searchResults.length > 0">
                <h2 class="mb-6 text-xl font-bold text-neutral-900 dark:text-white">Hasil Pencarian</h2>
                <div class="space-y-2">
                    <div v-for="result in searchResults" :key="result.id" class="flex items-center justify-between rounded-lg border border-neutral-200 p-4 dark:border-neutral-800">
                        <div>
                            <p class="font-semibold text-neutral-900 dark:text-white">{{ query }}{{ result.tld }}</p>
                            <p class="text-sm" :class="result.available ? 'text-green-600' : 'text-red-500'">
                                {{ result.available ? 'Tersedia' : 'Tidak tersedia' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-neutral-900 dark:text-white">{{ formatPrice(result.registration_price) }}</p>
                            <p class="text-xs text-neutral-500">/tahun</p>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <h2 class="mb-6 text-xl font-bold text-neutral-900 dark:text-white">Semua TLD</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                                <th class="pb-3 font-semibold text-neutral-900 dark:text-white">TLD</th>
                                <th class="pb-3 font-semibold text-neutral-900 dark:text-white">Registrasi</th>
                                <th class="pb-3 font-semibold text-neutral-900 dark:text-white">Perpanjang</th>
                                <th class="pb-3 font-semibold text-neutral-900 dark:text-white">Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tld in tlds" :key="tld.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                                <td class="py-3 font-medium text-neutral-900 dark:text-white">{{ tld.tld }}</td>
                                <td class="py-3 text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.registration_price) }}</td>
                                <td class="py-3 text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.renewal_price) }}</td>
                                <td class="py-3 text-neutral-600 dark:text-neutral-400">{{ formatPrice(tld.transfer_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </section>

    <section class="bg-neutral-50 py-12 dark:bg-neutral-900">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="flex gap-3">
                    <Shield class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600" />
                    <div>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Gratis DNS Management</h4>
                        <p class="text-sm text-neutral-500">Kelola DNS record dengan mudah via control panel.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Globe class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600" />
                    <div>
                        <h4 class="font-medium text-neutral-900 dark:text-white">URL & Email Forwarding</h4>
                        <p class="text-sm text-neutral-500">Forward domain ke URL lain atau alamat email.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Shield class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600" />
                    <div>
                        <h4 class="font-medium text-neutral-900 dark:text-white">Whois Privacy</h4>
                        <p class="text-sm text-neutral-500">Lindungi data pribadi Anda dari whois publik.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>