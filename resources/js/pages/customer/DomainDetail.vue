<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Globe, RefreshCw, Shield } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'My Domains', href: '/customer/domains' }, { title: 'Detail', href: '#' }] } });

const props = defineProps<{ domain: any }>();
const nsForm = useForm({ ns1: props.domain.nameservers?.[0] ?? '', ns2: props.domain.nameservers?.[1] ?? '', ns3: props.domain.nameservers?.[2] ?? '', ns4: props.domain.nameservers?.[3] ?? '' });
const renewForm = useForm({ years: 1 });
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head :title="`Domain: ${domain.domain_name}`" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ domain.domain_name }}</h1><p class="text-xs text-neutral-500">Registrar: {{ domain.registrar ?? 'N/A' }} • Expires: {{ domain.expiration_date }}</p></div>
            <span :class="domain.status === 'active' ? 'rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800' : 'rounded-full bg-neutral-100 px-3 py-1 text-sm font-medium text-neutral-800'">{{ domain.status }}</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Nameservers</h3>
                <form @submit.prevent="nsForm.put(`/customer/domains/${domain.id}/nameservers`)" class="space-y-3">
                    <div><label class="text-xs text-neutral-500">NS 1</label><input v-model="nsForm.ns1" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="ns1.example.com" /></div>
                    <div><label class="text-xs text-neutral-500">NS 2</label><input v-model="nsForm.ns2" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="ns2.example.com" /></div>
                    <div><label class="text-xs text-neutral-500">NS 3 (optional)</label><input v-model="nsForm.ns3" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                    <div><label class="text-xs text-neutral-500">NS 4 (optional)</label><input v-model="nsForm.ns4" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                    <button type="submit" :disabled="nsForm.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">Update Nameservers</button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Renew Domain</h3>
                    <form @submit.prevent="renewForm.post(`/customer/domains/${domain.id}/renew`)" class="flex gap-3">
                        <select v-model="renewForm.years" class="rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"><option :value="1">1 Year</option><option :value="2">2 Years</option><option :value="3">3 Years</option><option :value="5">5 Years</option></select>
                        <button type="submit" :disabled="renewForm.processing" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><RefreshCw class="h-4 w-4" /> Renew</button>
                    </form>
                </div>

                <div v-if="domain.contacts?.length" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 font-semibold text-neutral-900 dark:text-white">Contacts</h3>
                    <div v-for="c in domain.contacts" :key="c.id" class="border-b border-neutral-100 py-2 text-sm last:border-0 dark:border-neutral-800/50">
                        <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs uppercase dark:bg-neutral-800">{{ c.type }}</span>
                        <p class="mt-1 text-neutral-900 dark:text-white">{{ c.name }}</p>
                        <p class="text-neutral-500">{{ c.email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>