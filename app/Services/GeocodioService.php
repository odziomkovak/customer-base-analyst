<?php

namespace App\Services;

use App\DataTransferObjects\CensusData;
use App\Exceptions\GeocodioException;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Http;

class GeocodioService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.geocod.io/v1.11';

    public function __construct()
    {
        $this->apiKey = config('services.geocodio.key');
    }

    /**
     * @return BaseCollection<int, CensusData>
     */
    public function batchGeocode(Collection $stores): BaseCollection
    {
        $data = $stores->map(function (Store $store) {
            return [
                'street' => $store->street_address,
                'city' => $store->city,
                'state' => $store->state,
                'postal_code' => $store->postcode,
            ];
        });

        $response = Http::asJson()
            ->throw(fn ($response) => throw new GeocodioException(
                $response->json('error', 'Geocodio request failed'),
                $response->status(),
            ))
            ->post("{$this->baseUrl}/geocode?".http_build_query([
                'api_key' => $this->apiKey,
                'fields' => 'census,acs-demographics,acs-economics,acs-social',
            ]), $data->values()->toArray());

        return collect($response->json('results'))->map(function (array $result) {
            return $this->extractCensusData($result['response']['results'][0] ?? []);
        });
    }

    private function extractCensusData(array $result): CensusData
    {
        $acs = $result['fields']['acs'] ?? [];

        return new CensusData(
            medianHouseholdIncome: $acs['economics']['Median household income']['Total']['value'] ?? null,
            perCapitaIncome: $acs['economics']['Per capita income']['Total']['value'] ?? null,
            collegeDegreePct: $this->calculateCollegeDegreePercent($acs['social']['Population by minimum level of education'] ?? []),
            medianAge: $acs['demographics']['Median age']['Total']['value'] ?? null,
        );
    }

    private function calculateCollegeDegreePercent(array $education): ?float
    {
        $total = $education['Total']['value'] ?? 0;

        if ($total === 0) {
            return null;
        }

        $degreeKeys = ["Bachelor's degree", "Master's degree", 'Professional school degree', 'Doctorate degree'];
        $degreeHolders = 0;

        foreach (['Male', 'Female'] as $gender) {
            foreach ($degreeKeys as $key) {
                $degreeHolders += $education["$gender: $key"]['value'] ?? 0;
            }
        }

        return round($degreeHolders / $total, 3);
    }
}
