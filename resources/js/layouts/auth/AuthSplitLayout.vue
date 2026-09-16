<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Globe } from '@lucide/vue';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="relative hidden h-full flex-col justify-between p-10 text-white lg:flex">
            <div class="absolute inset-0 bg-blue-700" />
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-900 opacity-50" />
            <div class="relative z-20 flex items-center gap-3 text-lg font-medium">
                <AppLogoIcon />
                <span>{{ name }}</span>
            </div>
            <div class="relative z-20 space-y-4">
                <h2 class="text-2xl font-bold tracking-tight">Hosting Cepat, Domain Murah, VPS Handal</h2>
                <p class="text-blue-100 text-sm leading-relaxed max-w-sm">Platform hosting Indonesia dengan performa tinggi, provisioning otomatis, dan dukungan 24/7. Mulai dari Rp 25.000/bulan.</p>
                <div class="flex gap-4 mt-6">
                    <div class="flex items-center gap-2 text-sm text-blue-200"><Globe class="size-4" /> 99.9% Uptime</div>
                    <div class="flex items-center gap-2 text-sm text-blue-200"><Globe class="size-4" /> Support 24/7</div>
                </div>
            </div>
            <div class="relative z-20 text-xs text-blue-300">&copy; {{ new Date().getFullYear() }} HostKu</div>
        </div>
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">{{ title }}</h1>
                    <p class="text-muted-foreground text-sm" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
