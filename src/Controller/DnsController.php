<?php

namespace App\Controller;

use App\Request\DomainRequest;
use App\Service\DnsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DnsController extends AbstractController
{

    public function __construct(private DnsService $dnsService)
    {
    }

    #[Route('/get-dns', name: 'dns_get')]
    public function getDnsRecords(Request $request): JsonResponse
    {
        $domain = $request->get('domain');

        if (empty($domain)) {
            return new JsonResponse(['error' => 'Domain name is required.'], 400);
        }

        if (!$this->isValidDomain($domain)) {
            return new JsonResponse(['error' => 'Invalid domain name format.'], 400);
        }

        return new JsonResponse($this->dnsService->getDomainRecords($domain), 200);

    }

    function isValidDomain(string $domain): bool
    {
        $pattern = '/^(?!\-)(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i';
        return preg_match($pattern, $domain) === 1;
    }

}
