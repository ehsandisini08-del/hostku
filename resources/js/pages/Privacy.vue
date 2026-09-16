<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head title="Privacy Policy — HostKu" />

    <section class="py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-neutral-900 sm:text-4xl dark:text-white">Kebijakan Privasi</h1>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Terakhir diperbarui: 1 Januari 2025</p>

            <div class="prose prose-neutral mt-8 max-w-none dark:prose-invert">
                <h2>1. Informasi yang Dikumpulkan</h2>
                <p>Kami mengumpulkan: nama, email, nomor telepon, alamat, informasi pembayaran, dan data teknis (IP address, browser, log akses).</p>

                <h2>2. Penggunaan Informasi</h2>
                <p>Informasi digunakan untuk: menyediakan layanan, memproses pembayaran, komunikasi terkait akun, dan peningkatan layanan.</p>

                <h2>3. Penyimpanan Data</h2>
                <p>Data disimpan di server aman di Indonesia. Kami menerapkan enkripsi dan kontrol akses ketat.</p>

                <h2>4. Pembagian Data</h2>
                <p>Kami TIDAK menjual data Anda. Data hanya dibagikan dengan: payment gateway untuk pemrosesan pembayaran, domain registrar untuk registrasi domain, dan jika diwajibkan hukum.</p>

                <h2>5. Cookie</h2>
                <p>Kami menggunakan cookie esensial untuk operasi situs dan cookie analytics untuk memahami penggunaan layanan.</p>

                <h2>6. Hak Pengguna</h2>
                <p>Anda berhak: mengakses data, memperbaiki data, menghapus akun, dan menarik persetujuan. Hubungi support@hostku.id</p>

                <h2>7. Perubahan Kebijakan</h2>
                <p>Perubahan akan diumumkan via email atau notifikasi di website. Penggunaan lanjutan berarti menyetujui perubahan.</p>
            </div>
        </div>
    </section>
</template>