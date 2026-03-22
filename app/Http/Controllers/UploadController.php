<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Imports\StoreImport;
use Maatwebsite\Excel\Facades\Excel;

class UploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UploadRequest $request): void
    {
        Excel::import(
            import: new StoreImport,
            filePath: $request->validated('file'),
            readerType: \Maatwebsite\Excel\Excel::CSV);
    }
}
