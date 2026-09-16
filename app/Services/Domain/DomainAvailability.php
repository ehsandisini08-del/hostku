<?php

namespace App\Services\Domain;

readonly class DomainAvailability
{
    public function __construct(
        public string $domain,
        public bool $available,
        public bool $isPremium = false,
    ) {}
}
