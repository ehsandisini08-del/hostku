<?php

namespace Database\Seeders;

use App\Models\DomainPricing;
use App\Models\HostingPlan;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\VpsPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDomainTlds();
        $this->seedHostingPlans();
        $this->seedVpsPlans();
    }

    private function seedDomainTlds(): void
    {
        $tlds = [
            ['.com', 150000, 150000, 150000, false],
            ['.net', 160000, 160000, 160000, false],
            ['.org', 170000, 170000, 170000, false],
            ['.id', 100000, 100000, 100000, false],
            ['.co.id', 120000, 120000, 120000, false],
            ['.my.id', 25000, 25000, 25000, false],
            ['.web.id', 50000, 50000, 50000, false],
            ['.sch.id', 75000, 75000, 75000, false],
            ['.biz.id', 100000, 100000, 100000, false],
        ];

        foreach ($tlds as $i => [$tld, $reg, $renew, $transfer, $premium]) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug('domain-'.$tld)],
                [
                    'type' => 'domain',
                    'name' => $tld,
                    'description' => "Domain {$tld} registration",
                    'features' => [
                        'Free DNS Management',
                        'URL Forwarding',
                        'Email Forwarding',
                        '24/7 Support',
                    ],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );

            DomainPricing::firstOrCreate(
                ['product_id' => $product->id, 'tld' => $tld],
                [
                    'registration_price' => $reg,
                    'renewal_price' => $renew,
                    'transfer_price' => $transfer,
                    'min_years' => 1,
                    'max_years' => 10,
                    'is_premium' => $premium,
                ]
            );

            ProductPrice::firstOrCreate(
                ['product_id' => $product->id, 'billing_cycle' => 'annually'],
                [
                    'price' => $renew,
                    'setup_fee' => 0,
                ]
            );
        }
    }

    private function seedHostingPlans(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'desc' => 'Perfect for personal websites and blogs',
                'disk' => 5120,
                'bandwidth' => 100000,
                'websites' => 1,
                'databases' => 1,
                'emails' => 1,
                'monthly' => 25000,
                'annually' => 250000,
                'features' => [
                    '5 GB SSD Storage',
                    '1 Website',
                    '1 Database',
                    '1 Email Account',
                    'Free SSL Certificate',
                    'cPanel Control Panel',
                ],
            ],
            [
                'name' => 'Business',
                'desc' => 'Great for small businesses and professional sites',
                'disk' => 20480,
                'bandwidth' => 500000,
                'websites' => 5,
                'databases' => 5,
                'emails' => 10,
                'monthly' => 75000,
                'annually' => 750000,
                'features' => [
                    '20 GB SSD Storage',
                    '5 Websites',
                    '5 Databases',
                    '10 Email Accounts',
                    'Free SSL Certificate',
                    'cPanel Control Panel',
                    'Daily Backup',
                    'SSH Access',
                ],
            ],
            [
                'name' => 'Pro',
                'desc' => 'Ultimate power for high-traffic websites',
                'disk' => 51200,
                'bandwidth' => 1000000,
                'websites' => 0, // unlimited
                'databases' => 0,
                'emails' => 0,
                'monthly' => 150000,
                'annually' => 1500000,
                'features' => [
                    '50 GB SSD Storage',
                    'Unlimited Websites',
                    'Unlimited Databases',
                    'Unlimited Email Accounts',
                    'Free SSL Certificate',
                    'cPanel Control Panel',
                    'Daily Backup',
                    'SSH Access',
                    'Priority Support',
                    'Free Domain (.id)',
                ],
            ],
        ];

        foreach ($plans as $i => $plan) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($plan['name'].'-hosting')],
                [
                    'type' => 'hosting',
                    'name' => $plan['name'].' Hosting',
                    'description' => $plan['desc'],
                    'features' => $plan['features'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );

            HostingPlan::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'disk_space_mb' => $plan['disk'],
                    'bandwidth_mb' => $plan['bandwidth'],
                    'max_websites' => $plan['websites'],
                    'max_databases' => $plan['databases'],
                    'max_emails' => $plan['emails'],
                    'max_ftp' => $plan['websites'] * 2 ?: 0,
                    'max_subdomains' => $plan['websites'] * 3 ?: 0,
                    'server_type' => 'cpanel',
                ]
            );

            ProductPrice::firstOrCreate(
                ['product_id' => $product->id, 'billing_cycle' => 'monthly'],
                [
                    'price' => $plan['monthly'],
                    'setup_fee' => 0,
                ]
            );

            ProductPrice::firstOrCreate(
                ['product_id' => $product->id, 'billing_cycle' => 'annually'],
                [
                    'price' => $plan['annually'],
                    'setup_fee' => 0,
                ]
            );
        }
    }

    private function seedVpsPlans(): void
    {
        $plans = [
            [
                'name' => 'VPS-1',
                'desc' => 'Entry-level VPS for small applications',
                'cpu' => 1,
                'ram' => 1024,
                'disk' => 20480,
                'bandwidth' => 1000000,
                'monthly' => 100000,
                'annually' => 1000000,
                'sort' => 0,
            ],
            [
                'name' => 'VPS-2',
                'desc' => 'Balanced VPS for growing applications',
                'cpu' => 2,
                'ram' => 2048,
                'disk' => 40960,
                'bandwidth' => 2000000,
                'monthly' => 200000,
                'annually' => 2000000,
                'sort' => 1,
            ],
            [
                'name' => 'VPS-4',
                'desc' => 'Powerful VPS for production workloads',
                'cpu' => 4,
                'ram' => 4096,
                'disk' => 81920,
                'bandwidth' => 4000000,
                'monthly' => 400000,
                'annually' => 4000000,
                'sort' => 2,
            ],
            [
                'name' => 'VPS-8',
                'desc' => 'High-performance VPS for demanding applications',
                'cpu' => 8,
                'ram' => 8192,
                'disk' => 163840,
                'bandwidth' => 8000000,
                'monthly' => 800000,
                'annually' => 8000000,
                'sort' => 3,
            ],
        ];

        $features = [
            'Full Root Access',
            'SSD Storage',
            '1 IPv4 Address',
            '1 IPv6 Address',
            'KVM Virtualization',
            '99.9% Uptime SLA',
            'Proxmox Powered',
        ];

        foreach ($plans as $plan) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($plan['name'])],
                [
                    'type' => 'vps',
                    'name' => $plan['name'],
                    'description' => $plan['desc'],
                    'features' => $features,
                    'sort_order' => $plan['sort'],
                    'is_active' => true,
                ]
            );

            VpsPlan::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'cpu_cores' => $plan['cpu'],
                    'ram_mb' => $plan['ram'],
                    'disk_mb' => $plan['disk'],
                    'bandwidth_mb' => $plan['bandwidth'],
                    'ipv4_count' => 1,
                    'ipv6_count' => 1,
                    'os_templates' => ['ubuntu-22.04', 'ubuntu-24.04', 'debian-12', 'centos-9-stream', 'rocky-9'],
                    'network_bridge' => 'vmbr0',
                    'storage_pool' => 'local-lvm',
                ]
            );

            ProductPrice::firstOrCreate(
                ['product_id' => $product->id, 'billing_cycle' => 'monthly'],
                [
                    'price' => $plan['monthly'],
                    'setup_fee' => 25000,
                ]
            );

            ProductPrice::firstOrCreate(
                ['product_id' => $product->id, 'billing_cycle' => 'annually'],
                [
                    'price' => $plan['annually'],
                    'setup_fee' => 0,
                ]
            );
        }
    }
}
