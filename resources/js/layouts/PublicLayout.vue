<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login, register } from '@/routes';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Server, Globe, Monitor, Menu, X } from '@lucide/vue';
import { ref } from 'vue';

const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-neutral-950">
        <Head>
            <slot name="head" />
        </Head>

        <header class="sticky top-0 z-50 border-b border-neutral-200 bg-white/80 backdrop-blur-sm dark:border-neutral-800 dark:bg-neutral-950/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <Link href="/home" class="flex items-center gap-2 font-semibold text-neutral-900 dark:text-white">
                    <Globe class="h-6 w-6 text-blue-600" />
                    <span class="text-lg">HostKu</span>
                </Link>

                <nav class="hidden items-center gap-1 md:flex">
                    <Link href="/hosting" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white">Hosting</Link>
                    <Link href="/vps" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white">VPS</Link>
                    <Link href="/domain" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white">Domain</Link>
                    <Link href="/pricing" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white">Pricing</Link>
                    <Link href="/about" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white">About</Link>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <Link :href="login()" class="rounded-lg px-4 py-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white">Login</Link>
                    <Link :href="register()" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Register</Link>
                </div>

                <button @click="mobileMenuOpen = !mobileMenuOpen" class="rounded-md p-2 text-neutral-600 md:hidden dark:text-neutral-400">
                    <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                    <X v-else class="h-6 w-6" />
                </button>
            </div>

            <div v-if="mobileMenuOpen" class="border-t border-neutral-200 px-4 pb-4 pt-2 md:hidden dark:border-neutral-800">
                <nav class="flex flex-col gap-1">
                    <Link href="/hosting" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">Hosting</Link>
                    <Link href="/vps" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">VPS</Link>
                    <Link href="/domain" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">Domain</Link>
                    <Link href="/pricing" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">Pricing</Link>
                    <Link href="/about" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">About</Link>
                    <Link href="/faq" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">FAQ</Link>
                    <Link href="/contact" class="rounded-md px-3 py-2 text-sm text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800">Contact</Link>
                    <hr class="my-2 border-neutral-200 dark:border-neutral-800" />
                    <Link :href="login()" class="rounded-lg bg-neutral-100 px-4 py-2.5 text-center text-sm font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">Login</Link>
                    <Link :href="register()" class="rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-medium text-white">Register</Link>
                </nav>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Products</h3>
                        <ul class="space-y-2">
                            <li><Link href="/hosting" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Web Hosting</Link></li>
                            <li><Link href="/vps" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">VPS</Link></li>
                            <li><Link href="/domain" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Domain</Link></li>
                            <li><Link href="/pricing" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Pricing</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Company</h3>
                        <ul class="space-y-2">
                            <li><Link href="/about" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">About</Link></li>
                            <li><Link href="/contact" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Contact</Link></li>
                            <li><Link href="/faq" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">FAQ</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Documentation</a></li>
                            <li><a href="#" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Knowledge Base</a></li>
                            <li><Link href="/contact" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Support Ticket</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Legal</h3>
                        <ul class="space-y-2">
                            <li><Link href="/terms" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Terms of Service</Link></li>
                            <li><Link href="/privacy" class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Privacy Policy</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 border-t border-neutral-200 pt-8 dark:border-neutral-800">
                    <p class="text-center text-sm text-neutral-500 dark:text-neutral-400">&copy; {{ new Date().getFullYear() }} HostKu. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>