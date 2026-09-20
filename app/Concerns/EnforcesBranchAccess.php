<?php

namespace App\Concerns;

use App\Models\User;

/**
 * Shared single-record branch-ownership guard, used by services and
 * controllers alike to stop a non-super-admin acting on another branch's
 * record just by knowing/guessing its ID. Super-admins bypass entirely.
 */
trait EnforcesBranchAccess
{
    protected function assertBranchAccess(User $actor, ?int ...$branchIds): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        foreach ($branchIds as $branchId) {
            if ($branchId !== null && (int) $branchId === (int) $actor->branch_id) {
                return;
            }
        }

        abort(403, "You do not have access to this branch's records.");
    }
}
