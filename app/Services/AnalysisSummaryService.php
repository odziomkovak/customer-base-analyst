<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnalysisSummaryService
{
    /** @var list<string> */
    private const array METRICS = ['median_household_income', 'per_capita_income', 'college_degree_pct', 'median_age'];

    /**
     * Build an aggregated demographic summary of all geocoded stores.
     *
     * @return array{total_stores: int, by_ownership_type: array<string, array<string, mixed>>, overall: array<string, mixed>}
     */
    public function summarize(): array
    {
        $baseQuery = Store::query()->where('geocoded', true);

        $totalStores = $baseQuery->count();

        $byOwnership = [];
        $groups = (clone $baseQuery)->select('ownership_type')
            ->distinct()
            ->pluck('ownership_type');

        foreach ($groups as $ownershipType) {
            $groupQuery = (clone $baseQuery)->where('ownership_type', $ownershipType);
            $byOwnership[$ownershipType] = $this->aggregateGroup($groupQuery);
        }

        return [
            'total_stores' => $totalStores,
            'by_ownership_type' => $byOwnership,
            'overall' => $this->aggregateGroup(clone $baseQuery),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aggregateGroup(Builder $query): array
    {
        $result = ['count' => $query->count()];

        $aggregates = $this->queryAggregates(clone $query);

        foreach (self::METRICS as $metric) {
            if ($aggregates[$metric.'_count'] === 0) {
                $result[$metric] = null;

                continue;
            }

            $result[$metric] = [
                'mean' => round((float) $aggregates[$metric.'_avg'], 2),
                'median' => round($this->queryMedian(clone $query, $metric), 2),
                'min' => (float) $aggregates[$metric.'_min'],
                'max' => (float) $aggregates[$metric.'_max'],
                'std_dev' => round($this->queryStdDev(clone $query, $metric, (float) $aggregates[$metric.'_avg']), 2),
            ];
        }

        return $result;
    }

    /**
     * Run a single query to compute avg, min, max, and non-null count for all metrics.
     *
     * @return array<string, float|int|null>
     */
    private function queryAggregates(Builder $query): array
    {
        $selects = [];
        foreach (self::METRICS as $metric) {
            $selects[] = DB::raw('AVG('.$metric.') as '.$metric.'_avg');
            $selects[] = DB::raw('MIN('.$metric.') as '.$metric.'_min');
            $selects[] = DB::raw('MAX('.$metric.') as '.$metric.'_max');
            $selects[] = DB::raw('COUNT('.$metric.') as '.$metric.'_count');
        }

        $row = $query->select($selects)->first();

        if ($row === null) {
            return array_fill_keys(
                array_merge(
                    ...array_map(fn (string $m) => [$m.'_avg', $m.'_min', $m.'_max', $m.'_count'], self::METRICS)
                ),
                0,
            );
        }

        return $row->toArray();
    }

    private function queryMedian(Builder $query, string $metric): float
    {
        $count = (clone $query)->whereNotNull($metric)->count();

        if ($count === 0) {
            return 0.0;
        }

        $offset = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) (clone $query)->whereNotNull($metric)
                ->orderBy($metric)
                ->offset($offset)
                ->limit(1)
                ->value($metric);
        }

        $values = (clone $query)->whereNotNull($metric)
            ->orderBy($metric)
            ->offset($offset - 1)
            ->limit(2)
            ->pluck($metric);

        return ($values[0] + $values[1]) / 2;
    }

    private function queryStdDev(Builder $query, string $metric, float $mean): float
    {
        $variance = (float) (clone $query)->whereNotNull($metric)
            ->selectRaw('AVG(('.$metric.' - ?) * ('.$metric.' - ?)) as variance', [$mean, $mean])
            ->value('variance');

        return sqrt($variance);
    }
}
