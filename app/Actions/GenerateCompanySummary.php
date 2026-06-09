<?php

namespace App\Actions;

use App\Models\Company;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateCompanySummary
{
    public function handle(Company $company): string
    {
        $websiteContent = $this->fetchWebsiteContent($company);
        $summary = $this->callOllama($company->name, $websiteContent);

        $company->updateQuietly(['summary' => $summary]);

        return $summary;
    }

    private function fetchWebsiteContent(Company $company): ?string
    {
        if (empty($company->website)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($company->website);

            if (! $response->successful()) {
                Log::warning("Failed to fetch website for {$company->name}: {$response->status()}");

                return null;
            }

            $content = strip_tags($response->body());
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
            $content = mb_substr($content, 0, 8000);

            return $content;
        } catch (RequestException $e) {
            Log::warning("Website fetch error for {$company->name}: {$e->getMessage()}");

            return null;
        } catch (\Exception $e) {
            Log::warning("Unexpected error fetching website for {$company->name}: {$e->getMessage()}");

            return null;
        }
    }

    private function callOllama(string $companyName, ?string $websiteContent): string
    {
        $prompt = $this->buildPrompt($companyName, $websiteContent);

        $response = Http::timeout(90)->post(config('services.ollama.llm_url').'/api/generate', [
            'model' => config('services.ollama.llm_model'),
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Ollama API request failed: '.$response->status().' '.$response->body());
        }

        $summary = trim($response->json('response', ''));
        $summary = preg_replace('/<think>.*?<\/think>/s', '', $summary);

        return trim($summary);
    }

    private function buildPrompt(string $companyName, ?string $websiteContent): string
    {
        $content = $websiteContent ?? 'No website content available.';

        return <<<PROMPT
/no_think
You are a career research assistant. Write a concise company summary for a job seeker.

Company name: {$companyName}

Website content:
{$content}

Instructions:
- Write 3–5 short paragraphs, 400 words or fewer total
- Cover: what the company does, products/services, tech stack if evident, size/stage if evident, culture signals
- Plain prose, no bullet lists, no markdown formatting
- Do not invent facts; note what is unclear if information is limited

Summary:
PROMPT;
    }
}
