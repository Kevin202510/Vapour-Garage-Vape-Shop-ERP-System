<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

/**
 * Shared "which branch is this request operating on" resolution, used by
 * Web controllers that let super-admins pick a branch (via ?branch_id= or
 * the topbar switcher) while pinning every other role to their own branch.
 */
trait ResolvesActiveBranch
{
    protected function resolveEffectiveBranchId(User $user): ?int
    {
        if (! $user->isSuperAdmin()) {
            return $user->branch_id;
        }

        return request()->has('branch_id')
            ? (request('branch_id') ?: null)
            : $user->activeBranchId();
    }
}
