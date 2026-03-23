<?php

namespace App\Listeners;

use App\Events\StoresImported;
use App\Models\Store;
use App\Services\GeocodioService;
use Illuminate\Contracts\Queue\ShouldQueue;

class GeocodeStores implements ShouldQueue
{
    public function __construct(private readonly GeocodioService $geocodio) {}

    public function handle(StoresImported $event): void
    {
        Store::query()
            ->where('geocoded', '=', false)
            ->where('state', '=', 'CO') // geocoding just one state due to free API limits
            ->chunkById(100, function ($stores) {
                $results = $this->geocodio->batchGeocode($stores);

                $results->each(function ($censusData, $index) use ($stores) {
                    $stores->get($index)->update([
                        ...$censusData->toArray(),
                        'geocoded' => true,
                    ]);
                });
            });
    }
}
