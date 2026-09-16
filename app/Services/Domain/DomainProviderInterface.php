<?php

namespace App\Services\Domain;

interface DomainProviderInterface
{
    /**
     * @return array<array{tld: string, available: bool}>
     */
    public function searchDomain(string $domain): array;

    public function checkAvailability(string $domain): DomainAvailability;

    public function registerDomain(string $domain, array $contacts, array $options): array;

    public function renewDomain(string $domain, int $years): array;

    public function transferDomain(string $domain, string $authCode, array $contacts): array;

    public function getDomainInfo(string $domain): array;

    public function updateNameserver(string $domain, array $nameservers): array;

    /**
     * @return array<string, array{registration: float, renewal: float, transfer: float}>
     */
    public function getTldPricing(): array;

    public function getEppCode(string $domain): string;
}
