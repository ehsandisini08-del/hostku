<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';
defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Tickets', href: '/admin/tickets' }, { title: 'Detail', href: '#' }] } });
const props = defineProps<{ ticket: any }>();
const form = useForm({ message: '' });
</script>

<template>
    <Head :title="`Admin — Ticket: ${ticket.subject}`" />
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div><h1 class="text-xl font-semibold text-neutral-900 dark:text-white">{{ ticket.subject }}</h1><p class="text-xs text-neutral-400">By: {{ ticket.user?.name }} • Priority: {{ ticket.priority }} • Status: {{ ticket.status }}</p></div>
        <div class="space-y-3"><div v-for="msg in ticket.messages" :key="msg.id" class="max-w-lg rounded-xl p-3" :class="msg.user_id === $page.props.auth.user.id ? 'ml-auto bg-blue-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 dark:text-white'"><p class="text-sm">{{ msg.message }}</p><p class="mt-1 text-xs opacity-70">{{ msg.user?.name }} • {{ new Date(msg.created_at).toLocaleString() }}</p></div></div>
        <div v-if="ticket.status !== 'closed'" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
            <form @submit.prevent="form.post(`/admin/tickets/${ticket.id}/reply`, { onSuccess: () => form.reset() })" class="flex gap-3">
                <textarea v-model="form.message" rows="2" required class="flex-1 rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Reply..."></textarea>
                <button type="submit" :disabled="form.processing" class="flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Send class="h-4 w-4" /> Send</button>
            </form>
        </div>
    </div>
</template>