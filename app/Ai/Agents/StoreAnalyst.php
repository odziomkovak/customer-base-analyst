<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Timeout(240)]
class StoreAnalyst implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a sharp retail market analyst.
        You will receive an aggregated demographic summary of Starbucks store locations
        across the US, enriched with US Census ACS data at the census tract level.
        Be specific with numbers. Write clearly for a non-technical business audience.
        Format your response using HTML tags (h2, p, ul, li, strong) for readability.
        INSTRUCTIONS;
    }
}
