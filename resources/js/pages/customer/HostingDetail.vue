<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowUpRight,
    Check,
    CheckCircle2,
    Copy,
    Database,
    ExternalLink,
    FolderTree,
    Globe,
    Key,
    Lock,
    Mail,
    Plus,
    RefreshCw,
    Server,
    ShieldAlert,
    ShieldCheck,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/customer/dashboard' },
            { title: 'My Hosting', href: '/customer/hosting-services' },
            { title: 'Panel Hosting', href: '#' },
        ],
    },
});

const props = defineProps<{
    hosting: any;
    dnsCheck?: {
        domain: string;
        expected_ip: string;
        resolved_ip: string | null;
        is_pointed: boolean;
    };
    webmailUrl?: string;
    phpMyAdminUrl?: string;
}>();

const activeTab = ref<'overview' | 'sftp' | 'database' | 'dns' | 'email'>('overview');
const copiedField = ref<string | null>(null);

function copyText(text: string, field: string) {
    if (!text) return;
    navigator.clipboard.writeText(text);
    copiedField.value = field;
    setTimeout(() => {
        copiedField.value = null;
    }, 2000);
}

const formatMb = (mb: number) => (mb >= 1024 ? `${(mb / 1024).toFixed(0)} GB` : `${mb} MB`);

// Forms
const sftpForm = useForm({ password: '', password_confirmation: '' });
const dbForm = useForm({ password: '', password_confirmation: '' });
const phpForm = useForm({ php_version: props.hosting.php_version || '8.4' });
const isReissuingSsl = ref(false);

function submitSftpPassword() {
    sftpForm.post(`/customer/hosting-services/${props.hosting.id}/change-password`, {
        preserveScroll: true,
        onSuccess: () => sftpForm.reset(),
    });
}

function submitDbPassword() {
    dbForm.post(`/customer/hosting-services/${props.hosting.id}/database/reset-password`, {
        preserveScroll: true,
        onSuccess: () => dbForm.reset(),
    });
}

function submitPhpVersion() {
    phpForm.post(`/customer/hosting-services/${props.hosting.id}/php-version`, {
        preserveScroll: true,
    });
}

function triggerSslReissue() {
    isReissuingSsl.value = true;
    router.post(
        `/customer/hosting-services/${props.hosting.id}/ssl/reissue`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isReissuingSsl.value = false;
            },
        },
    );
}

function reloadDns() {
    router.reload({ only: ['dnsCheck'], preserveScroll: true });
}

// Email Management
const showNewEmailModal = ref(false);
const emailForm = useForm({
    username: '',
    password: '',
    quota_mb: 500,
});

function submitNewEmail() {
    emailForm.post(`/customer/hosting-services/${props.hosting.id}/emails`, {
        preserveScroll: true,
        onSuccess: () => {
            showNewEmailModal.value = false;
            emailForm.reset();
        },
    });
}

function deleteEmail(email: any) {
    if (confirm(`Hapus akun email "${email.email_address}"? Kotak pesan akan dihapus permanen.`)) {
        router.delete(`/customer/hosting-services/${props.hosting.id}/emails/${email.id}`, {
            preserveScroll: true,
        });
    }
}

const editingEmail = ref<any>(null);
const emailPwForm = useForm({ password: '' });

function openChangeEmailPw(email: any) {
    editingEmail.value = email;
    emailPwForm.reset();
}

function submitEmailPassword() {
    if (!editingEmail.value) return;
    emailPwForm.put(`/customer/hosting-services/${props.hosting.id}/emails/${editingEmail.value.id}/password`, {
        preserveScroll: true,
        onSuccess: () => {
            editingEmail.value = null;
            emailPwForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="`Panel — ${hosting.domain ?? hosting.username}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <!-- Header Panel -->
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            {{ hosting.domain ?? hosting.username }}
                        </h1>
                        <a
                            v-if="hosting.domain"
                            :href="`http://${hosting.domain}`"
                            target="_blank"
                            class="inline-flex items-center text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400"
                            title="Kunjungi Website"
                        >
                            <ExternalLink class="h-4 w-4" />
                        </a>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                        <span class="rounded bg-neutral-100 px-2 py-0.5 font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                            {{ hosting.plan?.product?.name ?? 'Hosting' }}
                        </span>
                        <span>•</span>
                        <span>Server: {{ hosting.server?.name ?? 'Custom Server' }} ({{ hosting.server_ip }})</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Status Badge -->
                    <span
                        :class="
                            hosting.service?.status === 'active'
                                ? 'bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300'
                                : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300'
                        "
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                    >
                        <span class="h-2 w-2 rounded-full" :class="hosting.service?.status === 'active' ? 'bg-green-500' : 'bg-neutral-400'"></span>
                        {{ hosting.service?.status === 'active' ? 'Layanan Aktif' : hosting.service?.status }}
                    </span>

                    <!-- SSL Status -->
                    <span
                        v-if="hosting.ssl_active"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-950/60 dark:text-blue-300"
                    >
                        <ShieldCheck class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" /> SSL Aktif
                    </span>
                    <button
                        v-else
                        @click="triggerSslReissue"
                        :disabled="isReissuingSsl"
                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 transition hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-300"
                    >
                        <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': isReissuingSsl }" />
                        {{ isReissuingSsl ? 'Menerbitkan SSL...' : 'Terbitkan SSL Gratis' }}
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="mt-6 flex flex-wrap gap-2 border-t border-neutral-100 pt-4 dark:border-neutral-800/80">
                <button
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'bg-blue-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-900'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                >
                    <Server class="h-4 w-4" /> Ringkasan
                </button>
                <button
                    @click="activeTab = 'sftp'"
                    :class="activeTab === 'sftp' ? 'bg-blue-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-900'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                >
                    <FolderTree class="h-4 w-4" /> FTP / SFTP File
                </button>
                <button
                    @click="activeTab = 'database'"
                    :class="activeTab === 'database' ? 'bg-blue-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-900'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                >
                    <Database class="h-4 w-4" /> Database MySQL
                </button>
                <button
                    @click="activeTab = 'dns'"
                    :class="activeTab === 'dns' ? 'bg-blue-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-900'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                >
                    <Globe class="h-4 w-4" /> Panduan DNS Record
                </button>
                <button
                    @click="activeTab = 'email'"
                    :class="activeTab === 'email' ? 'bg-blue-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-900'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                >
                    <Mail class="h-4 w-4" /> Email Bisnis
                </button>
            </div>
        </div>

        <!-- TAB 1: OVERVIEW -->
        <div v-if="activeTab === 'overview'" class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <!-- Server & Web Info -->
                <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 text-base font-semibold text-neutral-900 dark:text-white">Informasi Website & Server</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-neutral-100 p-4 dark:border-neutral-800">
                            <span class="text-xs text-neutral-500">Domain Utama</span>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ hosting.domain }}</span>
                                <a :href="`http://${hosting.domain}`" target="_blank" class="text-blue-600 hover:underline text-xs flex items-center gap-0.5">
                                    Buka <ArrowUpRight class="h-3 w-3" />
                                </a>
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-100 p-4 dark:border-neutral-800">
                            <span class="text-xs text-neutral-500">IP Server Hosting</span>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ hosting.server_ip }}</span>
                                <button @click="copyText(hosting.server_ip, 'ip')" class="text-xs text-neutral-500 hover:text-blue-600">
                                    <Check v-if="copiedField === 'ip'" class="h-3.5 w-3.5 text-green-600" />
                                    <Copy v-else class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-100 p-4 dark:border-neutral-800">
                            <span class="text-xs text-neutral-500">Document Root (Lokasi File Web)</span>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="font-mono text-xs text-neutral-800 dark:text-neutral-200">/var/www/{{ hosting.username }}/public_html</span>
                                <button @click="copyText(`/var/www/${hosting.username}/public_html`, 'root')" class="text-xs text-neutral-500 hover:text-blue-600">
                                    <Check v-if="copiedField === 'root'" class="h-3.5 w-3.5 text-green-600" />
                                    <Copy v-else class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-100 p-4 dark:border-neutral-800">
                            <span class="text-xs text-neutral-500">Web Server & PHP Version</span>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="font-semibold text-neutral-900 dark:text-white">Nginx + PHP {{ hosting.php_version ?? '8.4' }}</span>
                                <button @click="activeTab = 'sftp'" class="text-xs text-blue-600 hover:underline">Kelola File</button>
                            </div>
                        </div>
                    </div>

                    <!-- PHP Version Switcher -->
                    <div class="mt-6 rounded-xl bg-neutral-50 p-4 dark:bg-neutral-900">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white">Ganti Versi PHP</h4>
                                <p class="text-xs text-neutral-500">Sesuaikan versi PHP dengan kebutuhan CMS/framework website Anda (Laravel, WordPress, dll)</p>
                            </div>
                            <form @submit.prevent="submitPhpVersion" class="flex items-center gap-2">
                                <select
                                    v-model="phpForm.php_version"
                                    class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                >
                                    <option value="7.4">PHP 7.4</option>
                                    <option value="8.0">PHP 8.0</option>
                                    <option value="8.1">PHP 8.1</option>
                                    <option value="8.2">PHP 8.2</option>
                                    <option value="8.3">PHP 8.3</option>
                                    <option value="8.4">PHP 8.4</option>
                                </select>
                                <button
                                    type="submit"
                                    :disabled="phpForm.processing || phpForm.php_version === hosting.php_version"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ phpForm.processing ? 'Menyimpan...' : 'Terapkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Card: Resource Quota -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h3 class="mb-4 text-base font-semibold text-neutral-900 dark:text-white">Batasan Kuota Paket</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                            <dt class="text-neutral-500">Disk Space Storage</dt>
                            <dd class="font-medium text-neutral-900 dark:text-white">{{ formatMb(hosting.plan?.disk_space_mb ?? 5000) }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                            <dt class="text-neutral-500">Bandwidth</dt>
                            <dd class="font-medium text-neutral-900 dark:text-white">
                                {{ hosting.plan?.bandwidth_mb ? formatMb(hosting.plan.bandwidth_mb) : 'Unlimited' }}
                            </dd>
                        </div>
                        <div class="flex justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                            <dt class="text-neutral-500">Database MySQL</dt>
                            <dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.plan?.max_databases ?? 1 }} Database</dd>
                        </div>
                        <div class="flex justify-between border-b border-neutral-100 pb-2 dark:border-neutral-800">
                            <dt class="text-neutral-500">Akun Email Bisnis</dt>
                            <dd class="font-medium text-neutral-900 dark:text-white">{{ hosting.plan?.max_emails ?? 1 }} Akun</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-neutral-500">SSL Certificate</dt>
                            <dd class="font-medium text-green-600 dark:text-green-400">Gratis (Let's Encrypt)</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- TAB 2: SFTP / FILE ACCESS -->
        <div v-if="activeTab === 'sftp'" class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-2 text-base font-semibold text-neutral-900 dark:text-white">Kredensial Akses File (SFTP / FTP)</h3>
                <p class="mb-4 text-xs text-neutral-500">
                    Gunakan kredensial ini di aplikasi FTP favorit Anda (FileZilla, WinSCP, Cyberduck) untuk upload file HTML, PHP, WordPress, atau CMS Anda.
                </p>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Protokol</span>
                            <span class="font-semibold text-neutral-900 dark:text-white">SFTP (SSH File Transfer) / Port 22</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Host / Server IP</span>
                            <span class="font-mono text-neutral-900 dark:text-white">{{ hosting.server_ip }}</span>
                        </div>
                        <button @click="copyText(hosting.server_ip, 'sftp_host')" class="text-neutral-500 hover:text-blue-600">
                            <Check v-if="copiedField === 'sftp_host'" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Username</span>
                            <span class="font-mono text-neutral-900 dark:text-white">{{ hosting.username }}</span>
                        </div>
                        <button @click="copyText(hosting.username, 'sftp_user')" class="text-neutral-500 hover:text-blue-600">
                            <Check v-if="copiedField === 'sftp_user'" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Direktori Upload (Public HTML)</span>
                            <span class="font-mono text-xs text-neutral-900 dark:text-white">/var/www/{{ hosting.username }}/public_html</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ganti Password SFTP -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-2 text-base font-semibold text-neutral-900 dark:text-white">Ganti Password SFTP</h3>
                <p class="mb-4 text-xs text-neutral-500">Setel atau perbarui password untuk login upload file ke hosting.</p>

                <form @submit.prevent="submitSftpPassword" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Password Baru</label>
                        <input
                            v-model="sftpForm.password"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                            placeholder="Minimal 8 karakter"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Konfirmasi Password Baru</label>
                        <input
                            v-model="sftpForm.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                            placeholder="Ketik ulang password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="sftpForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Key class="h-4 w-4" /> {{ sftpForm.processing ? 'Menyimpan...' : 'Update Password SFTP' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 3: DATABASE MYSQL -->
        <div v-if="activeTab === 'database'" class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Database MySQL</h3>
                        <p class="text-xs text-neutral-500">Gunakan detail koneksi ini di file config website (wp-config.php, .env, dll)</p>
                    </div>
                    <a
                        :href="phpMyAdminUrl"
                        target="_blank"
                        class="inline-flex items-center gap-1 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700"
                    >
                        <ExternalLink class="h-3.5 w-3.5" /> Buka phpMyAdmin
                    </a>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Database Name</span>
                            <span class="font-mono font-semibold text-neutral-900 dark:text-white">{{ hosting.db_name ?? `h_${hosting.username}` }}</span>
                        </div>
                        <button @click="copyText(hosting.db_name ?? `h_${hosting.username}`, 'db_name')" class="text-neutral-500 hover:text-blue-600">
                            <Check v-if="copiedField === 'db_name'" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Database User</span>
                            <span class="font-mono font-semibold text-neutral-900 dark:text-white">{{ hosting.db_user ?? hosting.username }}</span>
                        </div>
                        <button @click="copyText(hosting.db_user ?? hosting.username, 'db_user')" class="text-neutral-500 hover:text-blue-600">
                            <Check v-if="copiedField === 'db_user'" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Host / Server</span>
                            <span class="font-mono font-semibold text-neutral-900 dark:text-white">localhost (127.0.0.1)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-neutral-50 p-3 dark:bg-neutral-900">
                        <div>
                            <span class="block text-xs text-neutral-500">Port</span>
                            <span class="font-mono font-semibold text-neutral-900 dark:text-white">3306</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ganti Password MySQL -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <h3 class="mb-2 text-base font-semibold text-neutral-900 dark:text-white">Reset Password Database MySQL</h3>
                <p class="mb-4 text-xs text-neutral-500">Ubah kata sandi untuk user database `{{ hosting.db_user ?? hosting.username }}`.</p>

                <form @submit.prevent="submitDbPassword" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Password Database Baru</label>
                        <input
                            v-model="dbForm.password"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                            placeholder="Minimal 8 karakter"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Konfirmasi Password Baru</label>
                        <input
                            v-model="dbForm.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                            placeholder="Ketik ulang password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="dbForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Key class="h-4 w-4" /> {{ dbForm.processing ? 'Menyimpan...' : 'Update Password Database' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 4: PANDUAN DNS RECORD -->
        <div v-if="activeTab === 'dns'" class="space-y-6">
            <!-- DNS Status Banner -->
            <div
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border p-5"
                :class="
                    dnsCheck?.is_pointed
                        ? 'border-green-200 bg-green-50/70 text-green-900 dark:border-green-900/50 dark:bg-green-950/20 dark:text-green-200'
                        : 'border-amber-200 bg-amber-50/70 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-200'
                "
            >
                <div class="flex items-center gap-3">
                    <CheckCircle2 v-if="dnsCheck?.is_pointed" class="h-6 w-6 text-green-600 dark:text-green-400" />
                    <AlertCircle v-else class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    <div>
                        <h4 class="font-bold">
                            {{ dnsCheck?.is_pointed ? 'Domain Sudah Terhubung dengan Benar!' : 'Domain Belum Mengarah ke Server' }}
                        </h4>
                        <p class="text-xs">
                            <span v-if="dnsCheck?.is_pointed">Domain {{ hosting.domain }} telah berhasil diarahkan ke IP server ({{ hosting.server_ip }}).</span>
                            <span v-else>
                                Domain saat ini mengarah ke IP: <b>{{ dnsCheck?.resolved_ip ?? 'Belum terdeteksi' }}</b> (harus mengarah ke <b>{{ hosting.server_ip }}</b>).
                            </span>
                        </p>
                    </div>
                </div>
                <button
                    @click="reloadDns"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-xs font-semibold text-neutral-700 shadow-xs hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200"
                >
                    <RefreshCw class="h-3.5 w-3.5" /> Periksa Ulang DNS
                </button>
            </div>

            <!-- Tabel Panduan DNS -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Daftar DNS Record yang Wajib Dipasang</h3>
                    <p class="text-xs text-neutral-500">
                        Buka panel registrar atau Cloudflare tempat Anda mengelola domain `{{ hosting.domain }}`, lalu tambahkan DNS record berikut:
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-neutral-500 dark:border-neutral-800">
                                <th class="pb-3 font-semibold">Tipe Record</th>
                                <th class="pb-3 font-semibold">Host / Name</th>
                                <th class="pb-3 font-semibold">Nilai / Target</th>
                                <th class="pb-3 font-semibold">Fungsi</th>
                                <th class="pb-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            <tr>
                                <td class="py-3"><span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">A</span></td>
                                <td class="py-3 font-mono font-medium">@</td>
                                <td class="py-3 font-mono">{{ hosting.server_ip }}</td>
                                <td class="py-3 text-xs text-neutral-500">Mengarahkan domain utama ke website Anda</td>
                                <td class="py-3 text-right">
                                    <button @click="copyText(hosting.server_ip, 'dns_a')" class="text-xs text-blue-600 hover:underline">
                                        {{ copiedField === 'dns_a' ? 'Disalin' : 'Salin Nilai' }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3"><span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-950 dark:text-purple-300">CNAME</span></td>
                                <td class="py-3 font-mono font-medium">www</td>
                                <td class="py-3 font-mono">{{ hosting.domain }}</td>
                                <td class="py-3 text-xs text-neutral-500">Mengarahkan www.{{ hosting.domain }} ke domain utama</td>
                                <td class="py-3 text-right">
                                    <button @click="copyText(hosting.domain, 'dns_cname')" class="text-xs text-blue-600 hover:underline">
                                        {{ copiedField === 'dns_cname' ? 'Disalin' : 'Salin Nilai' }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3"><span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">A</span></td>
                                <td class="py-3 font-mono font-medium">mail</td>
                                <td class="py-3 font-mono">{{ hosting.server_ip }}</td>
                                <td class="py-3 text-xs text-neutral-500">Server pengiriman & penerimaan email domain</td>
                                <td class="py-3 text-right">
                                    <button @click="copyText(hosting.server_ip, 'dns_mail_a')" class="text-xs text-blue-600 hover:underline">
                                        {{ copiedField === 'dns_mail_a' ? 'Disalin' : 'Salin Nilai' }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3"><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300">MX</span></td>
                                <td class="py-3 font-mono font-medium">@</td>
                                <td class="py-3 font-mono">mail.{{ hosting.domain }} (Priority: 10)</td>
                                <td class="py-3 text-xs text-neutral-500">Penerima email masuk untuk domain Anda</td>
                                <td class="py-3 text-right">
                                    <button @click="copyText(`mail.${hosting.domain}`, 'dns_mx')" class="text-xs text-blue-600 hover:underline">
                                        {{ copiedField === 'dns_mx' ? 'Disalin' : 'Salin Nilai' }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3"><span class="rounded bg-neutral-100 px-2 py-0.5 text-xs font-bold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">TXT</span></td>
                                <td class="py-3 font-mono font-medium">@</td>
                                <td class="py-3 font-mono text-xs">v=spf1 mx a ~all</td>
                                <td class="py-3 text-xs text-neutral-500">SPF record agar email tidak masuk folder spam</td>
                                <td class="py-3 text-right">
                                    <button @click="copyText('v=spf1 mx a ~all', 'dns_txt')" class="text-xs text-blue-600 hover:underline">
                                        {{ copiedField === 'dns_txt' ? 'Disalin' : 'Salin Nilai' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 5: EMAIL DOMAIN -->
        <div v-if="activeTab === 'email'" class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Kelola Akun Email Bisnis (@{{ hosting.domain }})</h3>
                    <p class="text-xs text-neutral-500">Buat dan kelola alamat email khusus dengan nama domain Anda sendiri</p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="webmailUrl"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 bg-white px-3.5 py-2 text-sm font-medium text-neutral-800 shadow-xs hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200"
                    >
                        <ExternalLink class="h-4 w-4" /> Buka Webmail
                    </a>
                    <button
                        @click="showNewEmailModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" /> Buat Email Baru
                    </button>
                </div>
            </div>

            <!-- Daftar Akun Email -->
            <div class="rounded-2xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 text-neutral-500 dark:border-neutral-800">
                            <th class="px-6 py-3 font-semibold">Alamat Email</th>
                            <th class="px-6 py-3 font-semibold">Kuota Penyimpanan</th>
                            <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        <tr v-for="em in hosting.emails" :key="em.id">
                            <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <Mail class="h-4 w-4 text-neutral-400" />
                                    <span>{{ em.email_address }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-500">{{ em.quota_mb }} MB</td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        @click="openChangeEmailPw(em)"
                                        class="rounded p-1.5 text-neutral-500 hover:bg-neutral-100 hover:text-blue-600 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                        title="Ubah Password Email"
                                    >
                                        <Key class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteEmail(em)"
                                        class="rounded p-1.5 text-neutral-500 hover:bg-neutral-100 hover:text-red-600 dark:hover:bg-neutral-800 dark:hover:text-red-400"
                                        title="Hapus Akun Email"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!hosting.emails || hosting.emails.length === 0">
                            <td colspan="3" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada akun email yang dibuat. Klik tombol <b>"Buat Email Baru"</b> di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Info Konfigurasi Klien Email (Outlook, Thunderbird, HP) -->
            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-6 dark:border-neutral-800 dark:bg-neutral-900/60">
                <h4 class="text-sm font-bold text-neutral-900 dark:text-white">Konfigurasi Email Client (HP / Outlook / Gmail)</h4>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 text-xs">
                    <div>
                        <span class="font-semibold text-neutral-800 dark:text-neutral-200">Server Email Masuk (IMAP):</span>
                        <ul class="mt-1 space-y-1 text-neutral-600 dark:text-neutral-400">
                            <li>• Host: <code class="font-mono text-blue-600 dark:text-blue-400">{{ hosting.server_ip }}</code> (atau mail.{{ hosting.domain }})</li>
                            <li>• Port: <b>993</b> (SSL/TLS)</li>
                            <li>• Username: Alamat email lengkap Anda</li>
                        </ul>
                    </div>
                    <div>
                        <span class="font-semibold text-neutral-800 dark:text-neutral-200">Server Email Keluar (SMTP):</span>
                        <ul class="mt-1 space-y-1 text-neutral-600 dark:text-neutral-400">
                            <li>• Host: <code class="font-mono text-blue-600 dark:text-blue-400">{{ hosting.server_ip }}</code> (atau mail.{{ hosting.domain }})</li>
                            <li>• Port: <b>587</b> (STARTTLS) atau <b>465</b> (SSL)</li>
                            <li>• Autentikasi: Diperlukan (Gunakan password email)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: BUAT EMAIL BARU -->
        <div v-if="showNewEmailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Buat Akun Email Baru</h3>
                    <button @click="showNewEmailModal = false" class="rounded p-1 text-neutral-400 hover:text-neutral-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitNewEmail" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Username Email</label>
                        <div class="mt-1 flex rounded-lg border border-neutral-300 dark:border-neutral-700 overflow-hidden">
                            <input
                                v-model="emailForm.username"
                                type="text"
                                required
                                placeholder="contoh: admin, info, kontak"
                                class="w-full border-0 px-3 py-2 text-sm focus:ring-0 dark:bg-neutral-800 dark:text-white"
                            />
                            <span class="flex items-center bg-neutral-100 px-3 text-xs font-medium text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 border-l dark:border-neutral-700">
                                @{{ hosting.domain }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Password Email</label>
                        <input
                            v-model="emailForm.password"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            placeholder="Minimal 8 karakter"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Kuota Penyimpanan (MB)</label>
                        <input
                            v-model.number="emailForm.quota_mb"
                            type="number"
                            min="50"
                            max="5000"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showNewEmailModal = false"
                            class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="emailForm.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ emailForm.processing ? 'Membuat...' : 'Buat Email' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: GANTI PASSWORD EMAIL -->
        <div v-if="editingEmail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Ubah Password Email</h3>
                    <button @click="editingEmail = null" class="rounded p-1 text-neutral-400 hover:text-neutral-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitEmailPassword" class="mt-4 space-y-4">
                    <div>
                        <span class="text-xs text-neutral-500">Akun:</span>
                        <p class="font-semibold text-neutral-900 dark:text-white">{{ editingEmail.email_address }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Password Baru</label>
                        <input
                            v-model="emailPwForm.password"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                            placeholder="Minimal 8 karakter"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="editingEmail = null"
                            class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="emailPwForm.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ emailPwForm.processing ? 'Menyimpan...' : 'Simpan Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
