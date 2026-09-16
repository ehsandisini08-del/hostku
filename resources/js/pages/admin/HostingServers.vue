<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Globe, Pencil, Plus, Terminal, Trash2, X } from '@lucide/vue';
import { ref, watch } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Hosting Servers', href: '#' }] } });

const props = defineProps<{ servers: { data: any[]; links: any; meta: any } }>();
const showForm = ref(false);
const editingServer = ref<any>(null);
const isSsh = ref(false);

const form = useForm({
    name: '',
    hostname: '',
    ip_address: '',
    panel_type: 'custom_ssh',
    api_url: '',
    api_token: '',
    api_username: '',
    max_accounts: null as number | null,
    is_active: true,
    ssh_port: 22,
    ssh_user: 'hostku-provision',
    ssh_key_path: '/var/www/hostku/storage/keys/hostku_provision',
    web_server: 'nginx',
    php_version: '8.4',
    base_path: '/var/www',
    ssl_email: '',
});

watch(() => form.panel_type, (val: string) => {
    isSsh.value = val === 'custom_ssh';
});

function openCreate() {
    editingServer.value = null;
    form.reset();
    form.clearErrors();
    form.panel_type = 'custom_ssh';
    form.ssh_port = 22;
    form.ssh_user = 'hostku-provision';
    form.ssh_key_path = '/var/www/hostku/storage/keys/hostku_provision';
    form.web_server = 'nginx';
    form.php_version = '8.4';
    form.base_path = '/var/www';
    form.is_active = true;
    isSsh.value = true;
    showForm.value = true;
}

function openEdit(s: any) {
    editingServer.value = s;
    form.clearErrors();
    form.name = s.name;
    form.hostname = s.hostname;
    form.ip_address = s.ip_address;
    form.panel_type = s.panel_type;
    form.api_url = s.api_url || '';
    form.api_token = '';
    form.api_username = s.api_username || '';
    form.max_accounts = s.max_accounts ?? null;
    form.is_active = Boolean(s.is_active);
    form.ssh_port = s.ssh_port ?? 22;
    form.ssh_user = s.ssh_user || 'hostku-provision';
    form.ssh_key_path = s.ssh_key_path || '/var/www/hostku/storage/keys/hostku_provision';
    form.web_server = s.web_server || 'nginx';
    form.php_version = s.php_version || '8.4';
    form.base_path = s.base_path || '/var/www';
    form.ssl_email = s.ssl_email || '';
    isSsh.value = s.panel_type === 'custom_ssh';
    showForm.value = true;
}

function submitForm() {
    if (editingServer.value) {
        form.put(`/admin/hosting-servers/${editingServer.value.id}`, {
            onSuccess: () => {
                showForm.value = false;
                editingServer.value = null;
                form.reset();
            },
        });
    } else {
        form.post('/admin/hosting-servers', {
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    }
}

function deleteServer(server: any) {
    if (confirm(`Apakah Anda yakin ingin menghapus server "${server.name}"?`)) {
        router.delete(`/admin/hosting-servers/${server.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Admin — Hosting Servers" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-neutral-900 dark:text-white">Hosting Servers</h1>
            <button
                v-if="!showForm"
                @click="openCreate"
                class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                <Plus class="h-4 w-4" /> Add Server
            </button>
        </div>

        <div v-if="showForm" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
            <div class="mb-4 flex items-center justify-between border-b border-neutral-200 pb-3 dark:border-neutral-800">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">
                    {{ editingServer ? `Edit Server: ${editingServer.name}` : 'Add New Hosting Server' }}
                </h2>
                <button @click="showForm = false; editingServer = null;" class="rounded-md p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="max-w-xl space-y-4">
                <div v-if="Object.keys(form.errors).length > 0" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/50 dark:text-red-300">
                    <ul class="list-disc pl-5">
                        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Name</label>
                        <input v-model="form.name" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="Server Jakarta 1" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Hostname</label>
                        <input v-model="form.hostname" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="srv1.hostku.id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">IP Address</label>
                        <input v-model="form.ip_address" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="103.123.45.67" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Provisioning Type</label>
                        <select v-model="form.panel_type" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                            <option value="custom_ssh">Custom (SSH + Nginx + PHP-FPM)</option>
                            <option value="cpanel">cPanel / WHM</option>
                            <option value="directadmin">DirectAdmin</option>
                        </select>
                    </div>
                </div>

                <template v-if="!isSsh">
                    <div class="border-t border-neutral-200 pt-3 dark:border-neutral-800">
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-neutral-400">
                            <Globe class="h-3.5 w-3.5" /> Panel API Config
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">API URL</label>
                                <input v-model="form.api_url" :required="!isSsh" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="https://server:2087" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">API Username</label>
                                <input v-model="form.api_username" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="root" />
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">API Token {{ editingServer ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                            <input v-model="form.api_token" :required="!isSsh && !editingServer" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="WHM API token" />
                        </div>
                    </div>
                </template>

                <template v-if="isSsh">
                    <div class="border-t border-neutral-200 pt-3 dark:border-neutral-800">
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-neutral-400">
                            <Terminal class="h-3.5 w-3.5" /> SSH Config
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">SSH Port</label>
                                <input v-model.number="form.ssh_port" type="number" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                            </div>
                            <div class="col-span-2">
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">SSH User</label>
                                <input v-model="form.ssh_user" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="hostku-provision" />
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">SSH Private Key Path</label>
                            <input v-model="form.ssh_key_path" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="/var/www/hostku/storage/keys/hostku_provision" />
                        </div>
                    </div>

                    <div class="border-t border-neutral-200 pt-3 dark:border-neutral-800">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-neutral-400">Server Config</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Web Server</label>
                                <select v-model="form.web_server" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                    <option value="nginx">Nginx</option>
                                    <option value="apache2">Apache2</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">PHP Version</label>
                                <select v-model="form.php_version" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                                    <option value="7.4">7.4</option>
                                    <option value="8.0">8.0</option>
                                    <option value="8.1">8.1</option>
                                    <option value="8.2">8.2</option>
                                    <option value="8.3">8.3</option>
                                    <option value="8.4">8.4</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Base Path</label>
                                <input v-model="form.base_path" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="/var/www" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">SSL Email (Let's Encrypt)</label>
                                <input v-model="form.ssl_email" type="email" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" placeholder="admin@hostku.id" />
                            </div>
                        </div>
                    </div>
                </template>

                <div class="border-t border-neutral-200 pt-3 dark:border-neutral-800">
                    <label class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        <span>Server Aktif</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button
                        :disabled="form.processing"
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : (editingServer ? 'Update Server' : 'Save Server') }}
                    </button>
                    <button
                        type="button"
                        @click="showForm = false; editingServer = null;"
                        class="rounded-lg border border-neutral-300 px-4 py-2.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-900"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 text-left dark:border-neutral-800">
                        <th class="px-4 py-3 text-neutral-500">Name</th>
                        <th class="px-4 py-3 text-neutral-500">Type</th>
                        <th class="px-4 py-3 text-neutral-500">Hostname</th>
                        <th class="px-4 py-3 text-neutral-500">IP</th>
                        <th class="px-4 py-3 text-neutral-500">Active</th>
                        <th class="px-4 py-3 text-right text-neutral-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in servers.data" :key="s.id" class="border-b border-neutral-100 dark:border-neutral-800/50">
                        <td class="px-4 py-3 font-medium text-neutral-900 dark:text-white">{{ s.name }}</td>
                        <td class="px-4 py-3">
                            <span :class="s.panel_type === 'custom_ssh' ? 'rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-950 dark:text-blue-300'">
                                <component :is="s.panel_type === 'custom_ssh' ? Terminal : Globe" class="mr-1 inline h-3 w-3" />
                                {{ s.panel_type === 'custom_ssh' ? 'Custom SSH' : s.panel_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-neutral-500">{{ s.hostname }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ s.ip_address }}</td>
                        <td class="px-4 py-3">
                            <span :class="s.is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500'">
                                {{ s.is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button
                                    @click="openEdit(s)"
                                    title="Edit Server"
                                    class="rounded p-1.5 text-neutral-500 hover:bg-neutral-100 hover:text-blue-600 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    @click="deleteServer(s)"
                                    title="Delete Server"
                                    class="rounded p-1.5 text-neutral-500 hover:bg-neutral-100 hover:text-red-600 dark:hover:bg-neutral-800 dark:hover:text-red-400"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="servers.data.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-neutral-500">
                            No hosting servers found. Click "Add Server" to configure one.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>