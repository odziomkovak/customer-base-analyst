<?php

namespace Tests\Feature;

use App\Events\StoresImported;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_requires_a_file(): void
    {
        $response = $this->post('/upload');

        $response->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_non_csv_files(): void
    {
        $file = UploadedFile::fake()->create('stores.pdf', 100, 'application/pdf');

        $response = $this->post('/upload', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_files_exceeding_max_size(): void
    {
        $file = UploadedFile::fake()->create('stores.csv', 11000, 'text/csv');

        $response = $this->post('/upload', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_upload_imports_csv_data(): void
    {
        Event::fake([StoresImported::class]);

        $csv = $this->createCsvFile([
            ['store_name', 'ownership_type', 'street_address', 'city', 'state', 'postcode', 'country'],
            ['Downtown Store', 'Company Owned', '123 Main St', 'Denver', 'CO', '80202', 'US'],
            ['Airport Store', 'Licensed', '456 Airport Blvd', 'Denver', 'CO', '80249', 'US'],
        ]);

        $this->post('/upload', ['file' => $csv]);

        $this->assertDatabaseCount('stores', 2);
        $this->assertDatabaseHas('stores', [
            'store_name' => 'Downtown Store',
            'ownership_type' => 'Company Owned',
            'city' => 'Denver',
            'state' => 'CO',
        ]);
    }

    public function test_upload_filters_non_us_stores(): void
    {
        Event::fake([StoresImported::class]);

        $csv = $this->createCsvFile([
            ['store_name', 'ownership_type', 'street_address', 'city', 'state', 'postcode', 'country'],
            ['US Store', 'Company Owned', '123 Main St', 'Denver', 'CO', '80202', 'US'],
            ['Canada Store', 'Licensed', '456 Maple Ave', 'Toronto', 'ON', 'M5V 2T6', 'CA'],
        ]);

        $this->post('/upload', ['file' => $csv]);

        $this->assertDatabaseCount('stores', 1);
        $this->assertDatabaseHas('stores', ['store_name' => 'US Store']);
        $this->assertDatabaseMissing('stores', ['store_name' => 'Canada Store']);
    }

    public function test_upload_dispatches_stores_imported_event(): void
    {
        Event::fake([StoresImported::class]);

        $csv = $this->createCsvFile([
            ['store_name', 'ownership_type', 'street_address', 'city', 'state', 'postcode', 'country'],
            ['Test Store', 'Company Owned', '123 Main St', 'Denver', 'CO', '80202', 'US'],
        ]);

        $this->post('/upload', ['file' => $csv]);

        Event::assertDispatched(StoresImported::class);
    }

    private function createCsvFile(array $rows): UploadedFile
    {
        $content = implode("\n", array_map(fn (array $row) => implode(',', $row), $rows));

        return UploadedFile::fake()->createWithContent('stores.csv', $content);
    }
}
