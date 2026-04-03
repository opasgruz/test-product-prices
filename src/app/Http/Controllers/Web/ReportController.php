<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\ProductCategory;
use Illuminate\View\View;

/**
 * Контроллер для отображения веб-интерфейса отчетов.
 */
class ReportController extends Controller
{
    /**
     * Отображает главную страницу отчетов со списком категорий.
     *
     * @return View
     */
    public function index(): View
    {
        /** @var ProductCategory[] $categories */
        $categories = ProductCategory::cases();

        return view('reports.index', compact('categories'));
    }
}
