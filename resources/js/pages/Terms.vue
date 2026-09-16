<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head title="Terms of Service — HostKu" />

    <section class="py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Syarat dan Ketentuan</h1>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Terakhir diperbarui: 1 Januari 2025</p>

            <div class="prose prose-neutral mt-8 max-w-none dark:prose-invert">
                <h2>1. Penerimaan Ketentuan</h2>
                <p>Dengan menggunakan layanan HostKu, Anda menyetujui syarat dan ketentuan ini. Jika tidak setuju, harap tidak menggunakan layanan kami.</p>

                <h2>2. Layanan</h2>
                <p>HostKu menyediakan layanan web hosting, domain registration, VPS, dan layanan terkait. Setiap layanan memiliki ketentuan spesifik yang dijelaskan saat pembelian.</p>

                <h2>3. Akun</h2>
                <p>Anda bertanggung jawab menjaga kerahasiaan kredensial akun. Semua aktivitas di akun Anda adalah tanggung jawab Anda.</p>

                <h2>4. Pembayaran</h2>
                <p>Pembayaran dilakukan di muka sesuai billing cycle yang dipilih. Kegagalan pembayaran dapat mengakibatkan suspensi atau terminasi layanan.</p>

                <h2>5. Refund</h2>
                <p>Garansi uang kembali 30 hari berlaku untuk shared hosting. Domain, VPS, dan dedicated server bersifat non-refundable.</p>

                <h2>6. Penggunaan yang Dilarang</h2>
                <p>Dilarang menggunakan layanan untuk aktivitas ilegal, spam, phishing, malware, atau konten yang melanggar hukum Indonesia.</p>

                <h2>7. Batasan Tanggung Jawab</h2>
                <p>HostKu tidak bertanggung jawab atas kerugian tidak langsung akibat penggunaan atau ketidakmampuan menggunakan layanan.</p>
            </div>
        </div>
    </section>
</template>