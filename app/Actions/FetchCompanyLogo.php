<?php

namespace App\Actions;

use App\Models\Company;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchCompanyLogo
{
    public function handle(Company $company): ?string
    {
        if (! blank($company->logo_url)) {
            return $company->logo_url;
        }

        $logoUrl = $this->fetchFromWebsite($company);

        if ($logoUrl === null) {
            return null;
        }

        $company->updateQuietly(['logo_url' => $logoUrl]);

        return $logoUrl;
    }

    private function fetchFromWebsite(Company $company): ?string
    {
        if (blank($company->website)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($company->website);

            if (! $response->successful()) {
                Log::warning("Failed to fetch website for logo ({$company->name}): {$response->status()}");
                return null;
            }

            $html = $response->body();

            return $this->parseLogoUrl($html, $company->website);
        } catch (RequestException $e) {
            Log::warning("Logo fetch request error for {$company->name}: {$e->getMessage()}");
            return null;
        } catch (\Exception $e) {
            Log::warning("Unexpected logo fetch error for {$company->name}: {$e->getMessage()}");
            return null;
        }
    }

    private function parseLogoUrl(string $html, string $baseUrl): ?string
    {
        $previous = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($html, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);

        // 1. og:image
        $ogImage = $xpath->query('//meta[@property="og:image"]/@content');
        if ($ogImage && $ogImage->length > 0) {
            $url = trim($ogImage->item(0)->nodeValue);
            if (! blank($url)) {
                return $this->toAbsoluteUrl($url, $baseUrl);
            }
        }

        // 2. og:logo
        $ogLogo = $xpath->query('//meta[@property="og:logo"]/@content');
        if ($ogLogo && $ogLogo->length > 0) {
            $url = trim($ogLogo->item(0)->nodeValue);
            if (! blank($url)) {
                return $this->toAbsoluteUrl($url, $baseUrl);
            }
        }

        // 3. favicon (<link rel="icon"> or rel="shortcut icon")
        $favicons = $xpath->query('//link[contains(@rel,"icon")]/@href');
        if ($favicons && $favicons->length > 0) {
            $url = trim($favicons->item(0)->nodeValue);
            if (! blank($url)) {
                return $this->toAbsoluteUrl($url, $baseUrl);
            }
        }

        return null;
    }

    private function toAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $parsed = parse_url($baseUrl);
        $origin = $parsed['scheme'] . '://' . $parsed['host'];

        if (str_starts_with($url, '//')) {
            return $parsed['scheme'] . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $origin . $url;
        }

        $basePath = isset($parsed['path']) ? dirname($parsed['path']) : '/';
        return $origin . rtrim($basePath, '/') . '/' . $url;
    }
}
