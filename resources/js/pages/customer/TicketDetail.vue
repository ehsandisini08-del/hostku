<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Tickets', href: '/customer/tickets' }, { title: 'Detail', href: '#' }] } });

const props = defineProps<{ ticket: any }>();

const form = useForm({ message: '' });

const statusColor = (s: string) => {
    const m: Record<string, string> = { open: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', answered: 'bg-blue-100 text-blue-800', closed: 'bg-neutral-100 text-neutral-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
</script>

<template>
    <Head :title="`Ticket: ${ticket.subject}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ ticket.subject }}</h1>
                <div class="mt-1 flex gap-2 text-xs">
                    <span :class="['rounded-full px-2 py-0.5 font-medium', statusColor(ticket.status)]">{{ ticket.status }}</span>
                    <span class="text-neutral-400">Priority: {{ ticket.priority }}</span>
                    <span class="text-neutral-400">Category: {{ ticket.category }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div v-for="msg in ticket.messages" :key="msg.id" class="flex gap-3" :class="msg.user_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'">
                <div class="max-w-lg rounded-xl p-4" :class="msg.user_id === $page.props.auth.user.id ? 'bg-blue-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 dark:text-white'">
                    <p class="text-sm">{{ msg.message }}</p>
                    <p class="mt-1 text-xs opacity-70">{{ msg.user?.name }} • {{ new Date(msg.created_at).toLocaleString() }}</p>
                </div>
            </div>
        </div>

        <div v-if="ticket.status !== 'closed'" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
            <form @submit.prevent="form.post(`/customer/tickets/${ticket.id}/reply`, { onSuccess: () => form.reset() })" class="flex gap-3">
                <textarea v-model="form.message" rows="2" required class="flex-1 rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Tulis balasan..."></textarea>
                <button type="submit" :disabled="form.processing" class="flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"><Send class="h-4 w-4" /> Send</button>
            </form>
        </div>
    </div>
</template>