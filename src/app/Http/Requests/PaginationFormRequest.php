<?php

namespace App\Http\Requests;

/**
 * Class PaginationFormRequest
 *
 * @property integer $per_page Кол-во данных которые мы хотим запросить
 * @property integer $page Номер страницы
 *
 * @package App\Http\Requests
 */
abstract class PaginationFormRequest extends BaseFormRequest
{
    /** @var int Максимальное кол-во отдаваемых данных */
    protected int $maxPerPage;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'min:1', 'max:' . $this->getMaxPerPage()],
            'page' => ['integer', 'min:1'],
        ];
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage ?? config('app.pagination.max_per_page');
    }
}
