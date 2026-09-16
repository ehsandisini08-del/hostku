<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MessageSquare, Plus } from '@lucide/vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/customer/dashboard' }, { title: 'Tickets', href: '/customer/tickets' }] } });

const props = defineProps<{ tickets: { data: any[]; links: any; meta: any } }>();
const statusColor = (s: string) => {
    const m: Record<string, string> = { open: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', answered: 'bg-blue-100 text-blue-800', closed: 'bg-neutral-100 text-neutral-800' };
    return m[s] ?? 'bg-neutral-100 text-neutral-800';
};
const priorityColor = (s: string) => {
    const m: Record<string, string> = { low: 'text-neutral-500', medium: 'text-yellow-600', high: 'text-orange-600', urgent: 'text-red-600' };
    return m[s] ?? 'text-neutral-500';
};
</script>

<template>
    <Head title="Tickets" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Support Tickets</h1>
            <Link href="/customer/tickets/create" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Plus class="h-4 w-4" /> New Ticket</Link>
        </div>

        <div v-if="tickets.data.length === 0" class="py-16 text-center">
            <MessageSquare class="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
            <p class="mt-4 text-neutral-500">Belum ada ticket.</p>
        </div>

        <div v-else class="space-y-3">
            <div v-for="ticket in tickets.data" :key="ticket.id" class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex items-center justify-between">
                    <div>
                        <Link :href="`/customer/tickets/${ticket.id}`" class="font-semibold text-neutral-900 hover:text-blue-600 dark:text-white">{{ ticket.subject }}</Link>
                        <div class="mt-1 flex gap-2 text-xs">
                            <span :class="['rounded-full px-2 py-0.5 font-medium', statusColor(ticket.status)]">{{ ticket.status }}</span>
                            <span :class="priorityColor(ticket.priority)">Priority: {{ ticket.priority }}</span>
                            <span class="text-neutral-400">{{ ticket.category }}</span>
                        </div>
                    </div>
                    <span class="text-xs text-neutral-400">{{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                </div>
            </div>
        </div>
    </div>
</template>