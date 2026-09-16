<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Coupons', href: '#' }] } });
const props = defineProps<{ coupons: { data: any[]; links: any; meta: any } }>();
const showForm = ref(false);
const formatPrice = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
const form = useForm({ code: '', type: 'percentage', value: 0, min_order: 0, max_usage: null as number|null, per_user_limit: 1 });
</script>

<template>
    <Head title="Admin — Coupons" />
    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Coupons</h1>
            <button @click="showForm = !showForm" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Plus class="h-4 w-4" /> Add</button>
        </div>
        <div v-if="showForm" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <form @submit.prevent="form.post('/admin/coupons', { onSuccess: () => showForm = false })" class="max-w-md space-y-3">
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Code</label><input v-model="form.code" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Type</label><select v-model="form.type" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Value</label><input v-model.number="form.value" type="number" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <button :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save</button>
            </form>
        </div>
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm"><thead><tr class="border-b border-neutral-200 text-left dark:border-neutral-800"><th class="px-4 py-3 text-neutral-500">Code</th><th class="px-4 py-3 text-neutral-500">Type</th><th class="px-4 py-3 text-neutral-500">Value</th><th class="px-4 py-3 text-neutral-500">Active</th></tr></thead>
                <tbody><tr v-for="c in coupons.data" :key="c.id" class="border-b border-neutral-100 dark:border-neutral-800/50"><td class="px-4 py-3 font-mono text-neutral-900 dark:text-white">{{ c.code }}</td><td class="px-4 py-3 text-neutral-500">{{ c.type }}</td><td class="px-4 py-3 text-neutral-500">{{ c.type === 'percentage' ? c.value+'%' : formatPrice(c.value) }}</td><td class="px-4 py-3"><span :class="c.is_active ? 'text-green-600' : 'text-red-500'">{{ c.is_active ? 'Yes' : 'No' }}</span></td></tr></tbody>
            </table>
        </div>
    </div>
</template>