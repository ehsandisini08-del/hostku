<?php

namespace App\Http\Controllers;

use App\Models\DomainPricing;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function home(): Response
    {
        $hostingPlans = Product::with(['hostingPlan', 'prices'])
            ->active()
            ->ofType('hosting')
            ->orderBy('sort_order')
            ->get();

        $vpsPlans = Product::with(['vpsPlan', 'prices'])
            ->active()
            ->ofType('vps')
            ->orderBy('sort_order')
            ->get();

        $popularTlds = DomainPricing::with('product.prices')
            ->where('is_premium', false)
            ->orderBy('registration_price')
            ->limit(6)
            ->get();

        return Inertia::render('Home', [
            'hostingPlans' => $hostingPlans,
            'vpsPlans' => $vpsPlans,
            'popularTlds' => $popularTlds,
        ]);
    }

    public function domain(): Response
    {
        $tlds = DomainPricing::with('product.prices')
            ->orderBy('registration_price')
            ->get();

        return Inertia::render('Domain', [
            'tlds' => $tlds,
        ]);
    }

    public function hosting(): Response
    {
        $plans = Product::with(['hostingPlan', 'prices'])
            ->active()
            ->ofType('hosting')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Hosting', [
            'plans' => $plans,
        ]);
    }

    public function vps(): Response
    {
        $plans = Product::with(['vpsPlan', 'prices'])
            ->active()
            ->ofType('vps')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Vps', [
            'plans' => $plans,
        ]);
    }

    public function pricing(): Response
    {
        $hostingPlans = Product::with(['hostingPlan', 'prices'])
            ->active()
            ->ofType('hosting')
            ->orderBy('sort_order')
            ->get();

        $vpsPlans = Product::with(['vpsPlan', 'prices'])
            ->active()
            ->ofType('vps')
            ->orderBy('sort_order')
            ->get();

        $domainTlds = DomainPricing::with('product.prices')
            ->orderBy('registration_price')
            ->get();

        return Inertia::render('Pricing', [
            'hostingPlans' => $hostingPlans,
            'vpsPlans' => $vpsPlans,
            'domainTlds' => $domainTlds,
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('About');
    }

    public function contact(): Response
    {
        return Inertia::render('Contact');
    }

    public function faq(): Response
    {
        $faqs = [
            [
                'q' => 'Apa itu web hosting?',
                'a' => 'Web hosting adalah layanan penyimpanan data dan file website Anda di server yang terhubung ke internet 24/7, sehingga website Anda dapat diakses kapan saja oleh pengunjung.',
            ],
            [
                'q' => 'Apa perbedaan shared hosting dan VPS?',
                'a' => 'Shared hosting berbagi resource server dengan pengguna lain (seperti apartemen), sedangkan VPS memberikan resource dedicated khusus untuk Anda (seperti rumah sendiri) dengan kontrol penuh via root access.',
            ],
            [
                'q' => 'Apakah saya bisa upgrade paket nanti?',
                'a' => 'Tentu! Anda dapat upgrade paket hosting atau VPS kapan saja. Untuk hosting, upgrade langsung diterapkan. Untuk VPS, resource akan ditambahkan setelah restart.',
            ],
            [
                'q' => 'Berapa lama domain aktif setelah pembelian?',
                'a' => 'Domain biasanya aktif dalam 1-24 jam setelah pendaftaran, meskipun sebagian besar domain aktif dalam hitungan menit.',
            ],
            [
                'q' => 'Apakah ada jaminan uptime?',
                'a' => 'Kami menjamin 99.9% uptime untuk semua layanan hosting dan VPS, didukung oleh infrastruktur enterprise dan tim monitoring 24/7.',
            ],
            [
                'q' => 'Metode pembayaran apa yang tersedia?',
                'a' => 'Kami menerima pembayaran via transfer bank (BCA, BNI, Mandiri, BRI), Virtual Account, QRIS, GoPay, dan gerai retail (Alfamart, Indomaret).',
            ],
            [
                'q' => 'Apakah saya bisa refund?',
                'a' => 'Kami menyediakan garansi uang kembali 30 hari untuk shared hosting. Untuk VPS dan domain, bersifat non-refundable kecuali terjadi kesalahan dari pihak kami.',
            ],
            [
                'q' => 'Bagaimana cara migrasi dari hosting lain?',
                'a' => 'Tim support kami akan membantu migrasi gratis! Cukup buka ticket support atau hubungi kami, dan kami akan memindahkan website Anda ke server kami.',
            ],
        ];

        return Inertia::render('Faq', [
            'faqs' => $faqs,
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Terms');
    }

    public function privacy(): Response
    {
        return Inertia::render('Privacy');
    }
}
