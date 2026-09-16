<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, RefreshCw, Wifi, WifiOff, ChevronDown, ChevronRight, Server } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Proxmox', href: '#' }] } });

const props = defineProps<{ servers: { data: any[]; links: any; meta: any } }>();
const showForm = ref(false);
const form = useForm({ name: '', host: '', port: 8006, auth_type: 'api_token', token_id: '', token_secret: '' });
const testingId = ref<number | null>(null);
const syncingId = ref<number | null>(null);
const expandedId = ref<number | null>(null);
const nodeDetails = ref<Record<number, any[]>>({});
const messages = ref<Record<number, { success: boolean; text: string }>>({});

const formatMb = (mb: number) => mb >= 1024 ? `${(mb / 1024).toFixed(1)} GB` : `${mb} MB`;
const formatDate = (d: string | null) => d ? new Date(d).toLocaleString() : 'Never';

function testConnection(server: any) {
    testingId.value = server.id;
    router.post(`/admin/proxmox/${server.id}/test`, {}, {
        preserveScroll: true,
        onFinish: () => { testingId.value = null; },
        onSuccess: (page: any) => {
            const resp = page.props.response;
            server.is_active = resp.is_active;
            server.last_checked_at = resp.last_checked_at;
            messages.value[server.id] = { success: resp.success, text: resp.message };
            setTimeout(() => { messages.value[server.id] = messages.value[server.id]!.text; }, 3000);
        },
    });
}

function syncNodes(server: any) {
    syncingId.value = server.id;
    router.post(`/admin/proxmox/${server.id}/sync`, {}, {
        preserveScroll: true,
        onFinish: () => { syncingId.value = null; },
        onSuccess: (page: any) => {
            const resp = page.props.response;
            server.nodes = resp.nodes;
            messages.value[server.id] = { success: resp.success, text: resp.message };
            setTimeout(() => delete messages.value[server.id], 3000);
        },
    });
}

function toggleDetail(serverId: number) {
    expandedId.value = expandedId.value === serverId ? null : serverId;
}
</script>

<template>
    <Head title="Admin — Proxmox" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Proxmox Servers</h1>
            <button @click="showForm = !showForm" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><Plus class="h-4 w-4" /> Add</button>
        </div>

        <div v-if="showForm" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <form @submit.prevent="form.post('/admin/proxmox', { onSuccess: () => showForm = false })" class="max-w-md space-y-3">
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Name</label><input v-model="form.name" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Host</label><input v-model="form.host" required placeholder="https://proxmox.example.com" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Port</label><input v-model.number="form.port" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Token ID</label><input v-model="form.token_id" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <div><label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Token Secret</label><input v-model="form.token_secret" required type="password" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" /></div>
                <button :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save</button>
            </form>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 text-neutral-500">Name</th>
                        <th class="px-4 py-3 text-neutral-500">Host</th>
                        <th class="px-4 py-3 text-neutral-500">Port</th>
                        <th class="px-4 py-3 text-neutral-500">Status</th>
                        <th class="px-4 py-3 text-neutral-500">Last Checked</th>
                        <th class="px-4 py-3 text-neutral-500 w-56">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="s in servers.data" :key="s.id">
                        <tr class="border-b border-neutral-100 dark:border-neutral-800/50" :class="expandedId === s.id ? 'bg-neutral-50 dark:bg-neutral-900/50' : ''">
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-white">{{ s.name }}</td>
                            <td class="px-4 py-3 text-neutral-500">{{ s.host }}</td>
                            <td class="px-4 py-3 text-neutral-500">{{ s.port }}</td>
                            <td class="px-4 py-3">
                                <span :class="s.is_active ? 'inline-flex items-center gap-1 text-green-600' : 'inline-flex items-center gap-1 text-red-500'">
                                    <component :is="s.is_active ? Wifi : WifiOff" class="h-3.5 w-3.5" />
                                    {{ s.is_active ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-neutral-400">{{ formatDate(s.last_checked_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button @click="testConnection(s)" :disabled="testingId === s.id"
                                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium"
                                        :class="s.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-red-50 text-red-700 hover:bg-red-100'">
                                        <RefreshCw :class="['h-3 w-3', testingId === s.id && 'animate-spin']" />
                                        {{ testingId === s.id ? '...' : 'Test' }}
                                    </button>
                                    <button @click="syncNodes(s)" :disabled="syncingId === s.id"
                                        class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                        <RefreshCw :class="['h-3 w-3', syncingId === s.id && 'animate-spin']" />
                                        {{ syncingId === s.id ? '...' : 'Sync' }}
                                    </button>
                                    <button @click="toggleDetail(s.id)"
                                        class="inline-flex items-center gap-1 rounded bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:bg-neutral-700">
                                        <component :is="expandedId === s.id ? ChevronDown : ChevronRight" class="h-3 w-3" />
                                        {{ s.nodes?.length ?? 0 }} nodes
                                    </button>
                                </div>
                                <p v-if="messages[s.id]" :class="['mt-1 text-xs', messages[s.id].success ? 'text-green-600' : 'text-red-500']">
                                    {{ messages[s.id].text ?? messages[s.id] }}
                                </p>
                            </td>
                        </tr>
                        <tr v-if="expandedId === s.id">
                            <td :colspan="6" class="bg-neutral-50 px-6 py-4 dark:bg-neutral-900/30">
                                <template v-if="s.nodes?.length">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-neutral-400">Nodes ({{ s.nodes.length }})</h4>
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="border-b text-left text-neutral-400 dark:border-neutral-700">
                                                <th class="py-1.5">Node</th>
                                                <th class="py-1.5">Status</th>
                                                <th class="py-1.5">CPU</th>
                                                <th class="py-1.5">RAM</th>
                                                <th class="py-1.5">Disk</th>
                                                <th class="py-1.5">Synced</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="n in s.nodes" :key="n.id" class="border-b border-neutral-100 dark:border-neutral-700/50">
                                                <td class="py-1.5 font-medium text-neutral-700 dark:text-neutral-300">{{ n.node_name }}</td>
                                                <td class="py-1.5">
                                                    <span :class="n.is_online ? 'inline-flex items-center gap-1 text-green-600' : 'inline-flex items-center gap-1 text-red-500'">
                                                        <component :is="n.is_online ? Wifi : WifiOff" class="h-3 w-3" />
                                                        {{ n.is_online ? 'Online' : 'Offline' }}
                                                    </span>
                                                </td>
                                                <td class="py-1.5 text-neutral-500">{{ n.cpu_used }}% / {{ n.cpu_total }}c</td>
                                                <td class="py-1.5 text-neutral-500">{{ formatMb(n.ram_used_mb) }} / {{ formatMb(n.ram_total_mb) }}</td>
                                                <td class="py-1.5 text-neutral-500">{{ formatMb(n.disk_used_mb) }} / {{ formatMb(n.disk_total_mb) }}</td>
                                                <td class="py-1.5 text-neutral-400">{{ formatDate(n.last_synced_at) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </template>
                                <div v-else class="py-4 text-center">
                                    <Server class="mx-auto h-6 w-6 text-neutral-300" />
                                    <p class="mt-2 text-xs text-neutral-400">No nodes synced yet. Click <strong>Sync</strong> to fetch nodes from the Proxmox API.</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>