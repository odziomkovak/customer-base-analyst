<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('app', [
            'hasStores' => Store::query()->exists(),
        ]);
    }
}
