<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

defineProps<{ faqs: Array<{ q: string; a: string }> }>();
</script>

<template>
    <Head title="FAQ — Pertanyaan Umum" />

    <section class="py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Pertanyaan Umum</h1>
            <p class="mt-3 text-neutral-600 dark:text-neutral-400">Jawaban untuk pertanyaan yang sering ditanyakan</p>

            <div class="mt-10 divide-y divide-neutral-200 dark:divide-neutral-800">
                <details v-for="faq in faqs" :key="faq.q" class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between text-lg font-medium text-neutral-900 dark:text-white">
                        {{ faq.q }}
                        <svg class="h-5 w-5 flex-shrink-0 text-neutral-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <p class="mt-3 text-neutral-600 dark:text-neutral-400">{{ faq.a }}</p>
                </details>
            </div>

            <div class="mt-12 rounded-xl bg-blue-50 p-6 text-center dark:bg-blue-950">
                <p class="text-neutral-900 dark:text-white">Masih punya pertanyaan?</p>
                <Link href="/contact" class="mt-2 inline-block font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Hubungi kami</Link>
            </div>
        </div>
    </section>
</template>