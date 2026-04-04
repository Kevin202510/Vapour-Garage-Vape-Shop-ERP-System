<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit users');
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255'],
            'password'     => ['sometimes', Password::min(8)->mixedCase()->numbers()],
            'branch_id'    => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'employee_id'  => ['sometimes', 'nullable', 'integer', 'exists:employees,id', "unique:users,employee_id,{$userId}"],
            'roles'        => ['sometimes', 'array'],
            'roles.*'      => ['string', 'exists:roles,name'],
            'permissions'  => ['sometimes', 'array'],
            'permissions.*'=> ['string', 'exists:permissions,name'],
            'is_active'    => ['sometimes', 'boolean'],
        ];
    }
}
