<?php

namespace Tests\Unit;

use App\DataTransferObjects\CensusData;
use PHPUnit\Framework\TestCase;

class CensusDataTest extends TestCase
{
    public function test_to_array_returns_all_fields(): void
    {
        $data = new CensusData(
            medianHouseholdIncome: 75000,
            perCapitaIncome: 35000,
            collegeDegreePct: 42.5,
            medianAge: 34.2,
        );

        $this->assertSame([
            'median_household_income' => 75000,
            'per_capita_income' => 35000,
            'college_degree_pct' => 42.5,
            'median_age' => 34.2,
        ], $data->toArray());
    }

    public function test_to_array_handles_null_values(): void
    {
        $data = new CensusData(
            medianHouseholdIncome: null,
            perCapitaIncome: null,
            collegeDegreePct: null,
            medianAge: null,
        );

        $this->assertSame([
            'median_household_income' => null,
            'per_capita_income' => null,
            'college_degree_pct' => null,
            'median_age' => null,
        ], $data->toArray());
    }

    public function test_properties_are_readonly(): void
    {
        $data = new CensusData(75000, 35000, 42.5, 34.2);

        $this->assertSame(75000, $data->medianHouseholdIncome);
        $this->assertSame(35000, $data->perCapitaIncome);
        $this->assertSame(42.5, $data->collegeDegreePct);
        $this->assertSame(34.2, $data->medianAge);
    }
}
