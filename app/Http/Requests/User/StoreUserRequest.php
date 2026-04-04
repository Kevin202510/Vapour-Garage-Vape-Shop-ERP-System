<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create users');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'password'    => ['required', Password::min(8)->mixedCase()->numbers()],
            'branch_id'   => ['nullable', 'integer', 'exists:branches,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id', 'unique:users,employee_id'],
            'roles'       => ['sometimes', 'array'],
            'roles.*'     => ['string', 'exists:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*'=> ['string', 'exists:permissions,name'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
