<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Mail, Phone, MapPin, Clock } from '@lucide/vue';
import { ref } from 'vue';

defineOptions({ layout: PublicLayout });

const form = ref({ name: '', email: '', subject: '', message: '' });
const submitted = ref(false);

function handleSubmit() {
    submitted.value = true;
    setTimeout(() => { submitted.value = false; form.value = { name: '', email: '', subject: '', message: '' }; }, 3000);
}
</script>

<template>
    <Head title="Contact — Hubungi HostKu" />

    <section class="py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Hubungi Kami</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Ada pertanyaan? Tim kami siap membantu.</p>

            <div class="mt-12 grid gap-12 lg:grid-cols-2">
                <div>
                    <div class="space-y-6">
                        <div class="flex gap-3">
                            <Mail class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">Email</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">support@hostku.id</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Phone class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">Telepon</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">+62 21 1234 5678</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <MapPin class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">Alamat</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Jl. Teknologi No. 42, Jakarta Selatan, Indonesia</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Clock class="h-5 w-5 text-blue-600" />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">Jam Operasional</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Senin - Jumat: 08:00 - 22:00 WIB<br>Sabtu - Minggu: 09:00 - 18:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Nama</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Email</label>
                        <input v-model="form.email" type="email" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Subjek</label>
                        <input v-model="form.subject" type="text" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300">Pesan</label>
                        <textarea v-model="form.message" rows="4" required class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"></textarea>
                    </div>
                    <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Kirim Pesan</button>
                    <p v-if="submitted" class="text-sm text-green-600">Pesan terkirim! Kami akan menghubungi Anda segera.</p>
                </form>
            </div>
        </div>
    </section>
</template>