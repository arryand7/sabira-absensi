<?php

namespace App\Policies;

use App\Models\ScheduleConflict;
use App\Models\User;

class ScheduleConflictPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    public function view(User $user, ScheduleConflict $conflict): bool
    {
        return $this->viewAny($user);
    }

    public function resolve(User $user, ScheduleConflict $conflict): bool
    {
        return $this->viewAny($user) && $conflict->isPending();
    }
}
