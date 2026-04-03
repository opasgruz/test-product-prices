<?php

namespace App\Http\Requests;

use App\Enums\ProductCategory;

/**
 * Class PaginationFormRequest
 *
 * @property integer $category_id Идентификатор категории
 *
 * @package App\Http\Requests
 */
class GenerateReportRequest extends BaseFormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        $categoryIds = array_map(fn($case) => $case->value, ProductCategory::cases());

        return [
            'category_id' => ['required', 'integer', \Illuminate\Validation\Rule::in($categoryIds)]
        ];
    }
}
