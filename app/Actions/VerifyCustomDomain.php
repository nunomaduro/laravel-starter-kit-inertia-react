<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\OrganizationDomain;

final readonly class VerifyCustomDomain
{
    public function handle(OrganizationDomain $domain): bool
    {
        $domain->increment('dns_check_attempts');
        $domain->last_dns_check_at = now();
        $domain->save();

        if ($domain->dns_check_attempts > 144) {
            $domain->update([
                'status' => 'error',
                'failure_reason' => 'timeout',
            ]);

            return false;
        }

        $records = @dns_get_record($domain->domain, DNS_CNAME);

        if ($records === false || $records === []) {
            return false;
        }

        foreach ($records as $record) {
            $target = mb_rtrim($record['target'] ?? '', '.');

            if ($this->isCloudflareProxy($target)) {
                $domain->update([
                    'status' => 'error',
                    'failure_reason' => 'cloudflare_proxy_detected',
                ]);

                return false;
            }

            if ($domain->cname_target && str_contains($target, mb_rtrim($domain->cname_target, '.'))) {
                $domain->update([
                    'status' => 'dns_verified',
                    'is_verified' => true,
                    'verified_at' => now(),
                ]);

                return true;
            }
        }

        return false;
    }

    private function isCloudflareProxy(string $target): bool
    {
        // Cloudflare proxy IPs resolve to *.cloudflare.com or cdn-cgi
        return str_contains($target, 'cloudflare') || str_contains($target, 'cdn-cgi');
    }
}
