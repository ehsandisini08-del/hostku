<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Pencil } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Products', href: '#' }] } });

const props = defineProps<{ products: { data: any[]; links: any; meta: any } }>();
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

const showForm = ref(false);
const form = useForm({ type: 'hosting', name: '', description: '', features: [] as string[], is_active: true, tld: '', registration_price: 0, renewal_price: 0, transfer_price: 0, disk_space_mb: 5000, max_websites: 1, max_databases: 1, max_emails: 1, cpu_cores: 1, ram_mb: 1024, disk_mb: 20480 });
</script>

<template>
    <Head title="Admin — Products" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Products</h1>
            <button @click="showForm = !showForm" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Plus class="h-4 w-4" /> Add</button>
        </div>

        <div v-if="showForm" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <form @submit.prevent="form.post('/admin/products', { onSuccess: () => showForm = false })" class="max-w-xl space-y-4">
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Type</label><select v-model="form.type" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"><option value="hosting">Hosting</option><option value="vps">VPS</option><option value="domain">Domain</option></select></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Name</label><input v-model="form.name" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div v-if="form.type === 'domain'">
                    <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">TLD</label><input v-model="form.tld" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder=".com" />
                    <label class="mt-2 text-sm font-medium text-neutral-700 dark:text-neutral-300">Reg Price</label><input v-model.number="form.registration_price" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                </div>
                <div v-else-if="form.type === 'hosting'">
                    <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Disk MB</label><input v-model.number="form.disk_space_mb" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                    <label class="mt-2 text-sm font-medium text-neutral-700 dark:text-neutral-300">Max Websites</label><input v-model.number="form.max_websites" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                </div>
                <div v-else-if="form.type === 'vps'">
                    <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">CPU Cores</label><input v-model.number="form.cpu_cores" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                    <label class="mt-2 text-sm font-medium text-neutral-700 dark:text-neutral-300">RAM MB</label><input v-model.number="form.ram_mb" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                </div>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">Save</button>
            </form>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Type</th><th class="px-4 py-3 text-neutral-500">Name</th><th class="px-4 py-3 text-neutral-500">Active</th></tr></thead>
                <tbody>
                    <tr v-for="p in products.data" :key="p.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3"><span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs">{{ p.type }}</span></td>
                        <td class="px-4 py-3 text-neutral-900 dark:text-white">{{ p.name }}</td>
                        <td class="px-4 py-3"><span :class="p.is_active ? 'text-green-600' : 'text-red-500'">{{ p.is_active ? 'Yes' : 'No' }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>