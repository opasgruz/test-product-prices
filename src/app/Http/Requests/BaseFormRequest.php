<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Базовая форма
 *
 * @package App\Http\Requests
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Возвращает инстанс валидатора
     *
     * @return Validator
     */
    public function getValidatorInstance()
    {
        return parent::getValidatorInstance();
    }

    /**
     * Манипулируем данными перед запуском валидации
     *
     * @param array $data
     *
     * @return array
     */
    protected function beforeValidate(array $data): array
    {
        return $data;
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        return $this->beforeValidate($data);
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function validator(): \Illuminate\Contracts\Validation\Validator
    {
        $factory = $this->container->make(ValidationFactory::class);
        $validator = $this->createDefaultValidator($factory);

        // Добавляем условную валидацию
        foreach ($this->conditionalValidateRules() as $conditionalRule) {
            $validator->sometimes(
                $conditionalRule['attributes'],
                $conditionalRule['rules'],
                $conditionalRule['callback']
            );
        }

        return $validator;
    }

    /**
     * Условные правила валидации
     *
     * @return array
     * [
     *  [
     *      'attributes' => ['startDateDeparture'],     // string|array
     *      'rules' => ['before:finishDateDeparture'],  // string|array
     *      'callback' => function ($input) {           // callable
     *          return !empty($input->finishDateDeparture);
     *      }
     *  ],
     *  ...
     * ]
     */
    protected function conditionalValidateRules(): array
    {
        return [];
    }
}
