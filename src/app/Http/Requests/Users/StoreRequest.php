<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

/**
 * Валидируем создание пользователя
 *
 * @property string $name
 * @property string $email
 * @property string $password
 *
 * @package App\Http\Requests\Users
 */
class StoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'unique:App\Models\SystemUser', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
