<?php

namespace App\Services\Domain;

use App\Models\Domain;
use App\Models\DomainContact;
use App\Models\DomainRegistrationLog;

class DomainService
{
    public function __construct(private DomainProviderInterface $provider) {}

    public function register(string $domainName, array $contacts = [], array $options = []): Domain
    {
        $log = DomainRegistrationLog::create([
            'action' => 'register',
            'status' => 'processing',
            'request_payload' => ['domain' => $domainName, 'contacts' => $contacts, 'options' => $options],
        ]);

        $result = $this->provider->registerDomain($domainName, $contacts, $options);

        $tld = '.'.explode('.', $domainName)[1] ?? 'com';

        $domain = Domain::create([
            'domain_name' => $domainName,
            'tld' => $tld,
            'registrar' => 'mock',
            'registrar_id' => $result['registrar_id'] ?? null,
            'status' => 'active',
            'registration_date' => now()->toDateString(),
            'expiration_date' => $result['expiration_date'] ?? now()->addYear()->toDateString(),
        ]);

        foreach ($contacts as $type => $contact) {
            DomainContact::create([
                'domain_id' => $domain->id,
                'type' => $type,
                'name' => $contact['name'] ?? '',
                'email' => $contact['email'] ?? '',
                'phone' => $contact['phone'] ?? null,
                'address' => $contact['address'] ?? null,
                'city' => $contact['city'] ?? null,
                'country' => $contact['country'] ?? 'ID',
            ]);
        }

        $log->update([
            'domain_id' => $domain->id,
            'status' => 'completed',
            'response_payload' => $result,
        ]);

        return $domain;
    }

    public function renew(Domain $domain, int $years = 1): Domain
    {
        $log = DomainRegistrationLog::create([
            'domain_id' => $domain->id,
            'action' => 'renew',
            'status' => 'processing',
        ]);

        $result = $this->provider->renewDomain($domain->domain_name, $years);

        $domain->update([
            'expiration_date' => $result['new_expiration_date'] ?? $domain->expiration_date->addYears($years),
        ]);

        $log->update(['status' => 'completed', 'response_payload' => $result]);

        return $domain;
    }

    public function transfer(string $domainName, string $authCode, array $contacts = []): Domain
    {
        DomainRegistrationLog::create([
            'action' => 'transfer',
            'status' => 'processing',
            'request_payload' => ['domain' => $domainName],
        ]);

        $this->provider->transferDomain($domainName, $authCode, $contacts);

        return $this->register($domainName, $contacts, []);
    }

    public function updateNameservers(Domain $domain, array $nameservers): Domain
    {
        $log = DomainRegistrationLog::create([
            'domain_id' => $domain->id,
            'action' => 'update_ns',
            'status' => 'processing',
            'request_payload' => ['nameservers' => $nameservers],
        ]);

        $result = $this->provider->updateNameserver($domain->domain_name, $nameservers);

        $domain->update(['nameservers' => $nameservers]);

        $log->update(['status' => 'completed', 'response_payload' => $result]);

        return $domain;
    }

    public function getInfo(Domain $domain): array
    {
        return $this->provider->getDomainInfo($domain->domain_name);
    }

    public function sync(Domain $domain): Domain
    {
        $info = $this->provider->getDomainInfo($domain->domain_name);

        $domain->update([
            'status' => $info['status'] ?? $domain->status,
            'expiration_date' => $info['expiration_date'] ?? $domain->expiration_date,
            'nameservers' => $info['nameservers'] ?? $domain->nameservers,
        ]);

        DomainRegistrationLog::create([
            'domain_id' => $domain->id,
            'action' => 'sync',
            'status' => 'completed',
            'response_payload' => $info,
        ]);

        return $domain;
    }
}
