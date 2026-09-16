<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Tickets', href: '/customer/tickets' }, { title: 'Create', href: '#' }] } });

const props = defineProps<{ services: any[] }>();

const form = useForm({
    subject: '',
    category: 'technical',
    priority: 'medium',
    message: '',
    service_id: '',
});
</script>

<template>
    <Head title="Create Ticket" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Create Ticket</h1>

        <form @submit.prevent="form.post('/customer/tickets')" class="max-w-2xl space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Subject</label>
                <input v-model="form.subject" type="text" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Brief summary of your issue" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Category</label>
                <select v-model="form.category" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                    <option value="technical">Technical</option>
                    <option value="billing">Billing</option>
                    <option value="sales">Sales</option>
                    <option value="general">General</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Priority</label>
                <select v-model="form.priority" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div v-if="services.length">
                <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Related Service (optional)</label>
                <select v-model="form.service_id" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                    <option value="">None</option>
                    <option v-for="s in services" :key="s.id" :value="s.id">#{{ s.id }} — {{ s.serviceable_type?.split('\\').pop() }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Message</label>
                <textarea v-model="form.message" rows="5" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Describe your issue in detail..."></textarea>
            </div>
            <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">{{ form.processing ? 'Creating...' : 'Create Ticket' }}</button>
        </form>
    </div>
</template>