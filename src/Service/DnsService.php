<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DnsService
{

    public function __construct(private CacheInterface $cache)
    {
    }

    public function getDomainRecords($domain): array|null
    {

        try {

            return $this->cache->get('dns_result' . $domain, function (ItemInterface $item) use ($domain) {

                $item->expiresAfter(3600); // 1 saat cache
                $result = dns_get_record($domain, DNS_A + DNS_AAAA + DNS_MX);

                if (empty($result)) {
                    return ['error' => sprintf('%s is not found.', $domain)];
                }

                return [$this->formatDnsRecords($result)];
            });

        } catch (\Exception $e) {
            return ['error' => 'Failed to execute DNS lookup.'];
        }

    }

    function formatDnsRecords(array $records): array
    {
        $result = [
            'A' => [],
            'AAAA' => [],
            'MX' => [],
        ];

        foreach ($records as $record) {
            switch ($record['type']) {

                case 'A':
                    $result['A'][] = [
                        'ip' => $record['ip'],
                        'ttl' => $record['ttl'],
                    ];
                    break;

                case 'AAAA':
                    $result['AAAA'][] = [
                        'ip' => $record['ipv6'] ?? null,
                        'ttl' => $record['ttl'],
                    ];
                    break;

                case 'MX':
                    $result['MX'][] = [
                        'priority' => $record['pri'],
                        'target' => $record['target'],
                        'ttl' => $record['ttl'],
                    ];
                    break;
            }
        }

        usort($result['MX'], fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $result;
    }

}
