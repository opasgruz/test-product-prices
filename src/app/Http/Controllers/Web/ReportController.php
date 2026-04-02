<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Enums\ProductCategory;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::cases();
        return view('reports.index', compact('categories'));
    }
}
