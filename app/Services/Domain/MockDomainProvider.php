<?php

namespace App\Services\Domain;

use App\Models\DomainPricing;

class MockDomainProvider implements DomainProviderInterface
{
    /** @var array<string, array{registration: float, renewal: float, transfer: float}> */
    private array $pricingCache = [];

    public function searchDomain(string $domain): array
    {
        $tlds = DomainPricing::with('product')
            ->where('is_premium', false)
            ->get();

        $results = [];
        foreach ($tlds as $tld) {
            $fullDomain = $domain.$tld->tld;
            $results[] = [
                'tld' => $tld->tld,
                'domain' => $fullDomain,
                'available' => $this->mockAvailability($fullDomain),
                'registration_price' => (float) $tld->registration_price,
                'renewal_price' => (float) $tld->renewal_price,
                'transfer_price' => (float) $tld->transfer_price,
            ];
        }

        return $results;
    }

    public function checkAvailability(string $domain): DomainAvailability
    {
        return new DomainAvailability(
            domain: $domain,
            available: $this->mockAvailability($domain),
        );
    }

    public function registerDomain(string $domain, array $contacts, array $options): array
    {
        return [
            'success' => true,
            'domain' => $domain,
            'registration_date' => now()->toDateString(),
            'expiration_date' => now()->addYear()->toDateString(),
            'registrar_id' => 'MOCK-'.fake()->uuid(),
        ];
    }

    public function renewDomain(string $domain, int $years): array
    {
        return [
            'success' => true,
            'domain' => $domain,
            'new_expiration_date' => now()->addYears($years)->toDateString(),
        ];
    }

    public function transferDomain(string $domain, string $authCode, array $contacts): array
    {
        return [
            'success' => true,
            'domain' => $domain,
            'message' => 'Transfer initiated',
        ];
    }

    public function getDomainInfo(string $domain): array
    {
        return [
            'domain' => $domain,
            'status' => 'active',
            'registration_date' => now()->subMonths(6)->toDateString(),
            'expiration_date' => now()->addMonths(6)->toDateString(),
            'nameservers' => ['ns1.example.com', 'ns2.example.com'],
        ];
    }

    public function updateNameserver(string $domain, array $nameservers): array
    {
        return [
            'success' => true,
            'domain' => $domain,
            'nameservers' => $nameservers,
        ];
    }

    public function getTldPricing(): array
    {
        if (! empty($this->pricingCache)) {
            return $this->pricingCache;
        }

        $tlds = DomainPricing::with('product')->get();
        foreach ($tlds as $tld) {
            $this->pricingCache[$tld->tld] = [
                'registration' => (float) $tld->registration_price,
                'renewal' => (float) $tld->renewal_price,
                'transfer' => (float) $tld->transfer_price,
            ];
        }

        return $this->pricingCache;
    }

    public function getEppCode(string $domain): string
    {
        return fake()->regexify('[A-Z0-9]{12}');
    }

    private function mockAvailability(string $domain): bool
    {
        return ! in_array($domain, ['google.com', 'facebook.com', 'amazon.com', 'apple.com']);
    }
}
