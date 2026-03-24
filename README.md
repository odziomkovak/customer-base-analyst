# Customer Base Analyst

A Laravel application that analyzes Starbucks store demographics by combining CSV data imports, geocoding enrichment via Geocodio, and AI-powered insights using Claude.

## Features

- **CSV Import** — Upload store location data (address, city, state, postcode, ownership type) via drag-and-drop
- **Geocoding & Census Enrichment** — Automatically enriches stores with US Census demographic data (median household income, per capita income, college degree %, median age) through the Geocodio API
- **AI-Powered Analysis** — Streams a Claude-generated demographic analysis covering executive summary, ownership type insights, market opportunity gaps, and ideal neighborhood profiles

## Tech Stack

- **Backend:** PHP 8.4, Laravel 13, SQLite
- **Frontend:** Tailwind CSS 4, Alpine.js 3, Vite
- **Services:** Geocodio (geocoding + census data), Anthropic Claude (AI analysis via Laravel AI)

## Requirements

- PHP 8.4+
- Composer
- Node.js & npm
- [Geocodio API key](https://www.geocod.io/)
- [Anthropic API key](https://console.anthropic.com/)

## Setup

```bash
git clone <repository-url>
cd customer-base-analyst

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Add your API keys to `.env`:

```
GEOCODIO_API_KEY=your-geocodio-key
ANTHROPIC_API_KEY=your-anthropic-key
```

Run migrations and build assets:

```bash
php artisan migrate
npm run build
```

## Running the Application

Using Laravel Herd, the app is available at `https://customer-base-analyst.test`.

Alternatively:

```bash
composer run dev
```

## Usage

1. **Upload** a CSV file containing Starbucks store data (columns: store name, ownership type, street address, city, state, postcode)
2. Stores are imported and automatically **geocoded** with census demographics (currently limited to Colorado stores on the free Geocodio tier)
3. Click **"Analyze existing data"** to stream an AI-generated demographic analysis

## Project Structure

```
app/
├── Ai/Agents/StoreAnalyst.php         # Claude AI agent for analysis
├── DataTransferObjects/CensusData.php  # Census data DTO
├── Events/StoresImported.php           # Post-import event
├── Http/Controllers/
│   ├── AnalysisController.php          # Streams AI analysis
│   ├── DashboardController.php         # Main page
│   └── UploadController.php            # CSV import
├── Imports/StoreImport.php             # CSV parser (Maatwebsite Excel)
├── Listeners/GeocodeStores.php         # Geocoding listener
├── Models/Store.php                    # Store model
└── Services/
    ├── AnalysisSummaryService.php      # Demographic data aggregation
    └── GeocodioService.php             # Geocodio API client
```

## Testing

```bash
php artisan test --compact
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).