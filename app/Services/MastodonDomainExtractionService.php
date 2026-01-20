<?php

namespace App\Services;

class MastodonDomainExtractionService
{
    private const string HTTP_PREFIX = 'https://';

    private const string REPLACE_PREFIX = 'http://';

    public function formatDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if (empty($domain)) {
            return '';
        }

        $domain = $this->removeProtocolFromUrl($domain);
        $domain = $this->removePathFromUrl($domain);
        $domain = $this->extractUrlFromUserHandleFormat($domain);

        return $this->forceHttps($domain);
    }

    private function extractUrlFromUserHandleFormat(string $domain): string
    {
        if (str_contains($domain, '@')) {
            $domain = last(explode('@', $domain));
        }

        return $domain;
    }

    private function removePathFromUrl(string $domain): string
    {
        return explode('/', $domain)[0];
    }

    private function removeProtocolFromUrl(string $domain): string
    {
        return preg_replace('/^https?:\/\//', '', $domain);
    }

    private function forceHttps(string $domain): string
    {
        $domain = str_replace(self::REPLACE_PREFIX, self::HTTP_PREFIX, $domain);
        if (!str_starts_with($domain, self::HTTP_PREFIX)) {
            $domain = self::HTTP_PREFIX . $domain;
        }

        return $domain;
    }
}
