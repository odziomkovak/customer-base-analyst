<?php

namespace App\DataTransferObjects;

class CensusData
{
    public function __construct(
        public readonly ?int $medianHouseholdIncome,
        public readonly ?int $perCapitaIncome,
        public readonly ?float $collegeDegreePct,
        public readonly ?float $medianAge,
    ) {}

    /**
     * @return array<string, int|float|null>
     */
    public function toArray(): array
    {
        return [
            'median_household_income' => $this->medianHouseholdIncome,
            'per_capita_income' => $this->perCapitaIncome,
            'college_degree_pct' => $this->collegeDegreePct,
            'median_age' => $this->medianAge,
        ];
    }
}
