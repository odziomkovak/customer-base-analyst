<?php

namespace App\Http\Controllers;

use App\Ai\Agents\StoreAnalyst;
use App\Services\AnalysisSummaryService;
use Laravel\Ai\Responses\StreamableAgentResponse;

class AnalysisController extends Controller
{
    public function __invoke(AnalysisSummaryService $summaryService): StreamableAgentResponse
    {
        $summary = $summaryService->summarize();

        $json = json_encode($summary, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        Below is an aggregated demographic summary of {$summary['total_stores']} Starbucks
        store locations, enriched with US Census ACS data at the census tract level.

        {$json}

        Please provide:

        1. **Executive Summary** (1 paragraph) — plain English profile of where
           Starbucks stores are located demographically. What kind of neighborhoods
           does Starbucks call home?

        2. **Ownership Type Insight** — what does the demographic difference between
           Company Owned and Licensed stores tell us about Starbucks' strategy?

        3. **Two Market Opportunity Gaps** — based on the data, where is Starbucks
           underrepresented relative to its apparent target demographic?

        4. **The Ideal Starbucks Neighborhood** — one punchy paragraph describing
           the archetypal census tract where a Starbucks is most likely to be found.

        Be specific with numbers. Write clearly for a non-technical business audience.
        The whole output should not be more than 300 words.
        PROMPT;

        return (new StoreAnalyst)->stream($prompt);
    }
}
