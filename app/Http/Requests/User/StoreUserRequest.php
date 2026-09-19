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
            'is_active'   => ['sometimes', 'boolean'],
            // Role/permission assignment intentionally NOT accepted here — it must
            // go through PUT /api/users/{user}/roles|permissions, which are gated
            // by the dedicated 'manage roles'/'manage permissions' abilities.
            // Accepting them via this 'create users'-gated endpoint would let a
            // lower-privileged user grant themselves or others arbitrary roles.
        ];
    }
}
