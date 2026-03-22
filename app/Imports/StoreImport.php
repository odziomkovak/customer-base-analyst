<?php

namespace App\Imports;

use App\Models\Store;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StoreImport implements WithChunkReading, ToCollection, WithHeadingRow
{
    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $collection): void
    {
        $insertData = $collection
            ->filter(fn($row) => $row['country'] === 'US')
            ->map(function($row) {
                return [
                    'store_name' => $row['store_name'],
                    'ownership_type' => $row['ownership_type'],
                    'street_address' => $row['street_address'],
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'postcode' => $row['postcode'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

        Store::query()->insert($insertData->toArray());
    }
}
