<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Services\AnalysisSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalysisSummaryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AnalysisSummaryService;
    }

    public function test_summarize_returns_zero_when_no_stores_exist(): void
    {
        $result = $this->service->summarize();

        $this->assertSame(0, $result['total_stores']);
        $this->assertEmpty($result['by_ownership_type']);
        $this->assertSame(0, $result['overall']['count']);
    }

    public function test_summarize_excludes_non_geocoded_stores(): void
    {
        Store::factory()->count(3)->create();
        Store::factory()->geocoded()->create();

        $result = $this->service->summarize();

        $this->assertSame(1, $result['total_stores']);
    }

    public function test_summarize_groups_by_ownership_type(): void
    {
        Store::factory()->companyOwned()->geocoded()->count(2)->create();
        Store::factory()->licensed()->geocoded()->count(3)->create();

        $result = $this->service->summarize();

        $this->assertSame(5, $result['total_stores']);
        $this->assertArrayHasKey('Company Owned', $result['by_ownership_type']);
        $this->assertArrayHasKey('Licensed', $result['by_ownership_type']);
        $this->assertSame(2, $result['by_ownership_type']['Company Owned']['count']);
        $this->assertSame(3, $result['by_ownership_type']['Licensed']['count']);
    }

    public function test_summarize_computes_correct_mean(): void
    {
        Store::factory()->geocoded(medianHouseholdIncome: 50000)->create();
        Store::factory()->geocoded(medianHouseholdIncome: 100000)->create();

        $result = $this->service->summarize();

        $this->assertSame(75000.0, $result['overall']['median_household_income']['mean']);
    }

    public function test_summarize_computes_correct_min_and_max(): void
    {
        Store::factory()->geocoded(perCapitaIncome: 20000)->create();
        Store::factory()->geocoded(perCapitaIncome: 60000)->create();
        Store::factory()->geocoded(perCapitaIncome: 40000)->create();

        $result = $this->service->summarize();

        $this->assertSame(20000.0, $result['overall']['per_capita_income']['min']);
        $this->assertSame(60000.0, $result['overall']['per_capita_income']['max']);
    }

    public function test_summarize_computes_median_with_odd_count(): void
    {
        Store::factory()->geocoded(medianAge: 25.0)->create();
        Store::factory()->geocoded(medianAge: 35.0)->create();
        Store::factory()->geocoded(medianAge: 45.0)->create();

        $result = $this->service->summarize();

        $this->assertSame(35.0, $result['overall']['median_age']['median']);
    }

    public function test_summarize_computes_median_with_even_count(): void
    {
        Store::factory()->geocoded(medianAge: 20.0)->create();
        Store::factory()->geocoded(medianAge: 30.0)->create();
        Store::factory()->geocoded(medianAge: 40.0)->create();
        Store::factory()->geocoded(medianAge: 50.0)->create();

        $result = $this->service->summarize();

        $this->assertSame(35.0, $result['overall']['median_age']['median']);
    }

    public function test_summarize_computes_standard_deviation(): void
    {
        Store::factory()->geocoded(collegeDegreePct: 10.0)->create();
        Store::factory()->geocoded(collegeDegreePct: 20.0)->create();
        Store::factory()->geocoded(collegeDegreePct: 30.0)->create();

        $result = $this->service->summarize();

        // Population std dev of [10, 20, 30]: mean=20, variance=((100+0+100)/3)=66.67, sqrt=8.16
        $this->assertEqualsWithDelta(8.16, $result['overall']['college_degree_pct']['std_dev'], 0.01);
    }

    public function test_summarize_handles_null_metric_values(): void
    {
        Store::factory()->geocoded(medianHouseholdIncome: 50000)->state(['median_age' => null])->create();
        Store::factory()->geocoded(medianHouseholdIncome: 70000)->state(['median_age' => null])->create();

        $result = $this->service->summarize();

        $this->assertNotNull($result['overall']['median_household_income']);
        $this->assertNull($result['overall']['median_age']);
    }

    public function test_summarize_with_single_store(): void
    {
        Store::factory()->geocoded(
            medianHouseholdIncome: 80000,
            perCapitaIncome: 40000,
            collegeDegreePct: 55.0,
            medianAge: 32.0,
        )->create();

        $result = $this->service->summarize();

        $this->assertSame(1, $result['total_stores']);
        $this->assertSame(80000.0, $result['overall']['median_household_income']['mean']);
        $this->assertSame(80000.0, $result['overall']['median_household_income']['median']);
        $this->assertSame(80000.0, $result['overall']['median_household_income']['min']);
        $this->assertSame(80000.0, $result['overall']['median_household_income']['max']);
        $this->assertSame(0.0, $result['overall']['median_household_income']['std_dev']);
    }

    public function test_overall_aggregation_spans_all_ownership_types(): void
    {
        Store::factory()->companyOwned()->geocoded(medianHouseholdIncome: 60000)->create();
        Store::factory()->licensed()->geocoded(medianHouseholdIncome: 80000)->create();

        $result = $this->service->summarize();

        $this->assertSame(2, $result['overall']['count']);
        $this->assertSame(70000.0, $result['overall']['median_household_income']['mean']);
    }
}
