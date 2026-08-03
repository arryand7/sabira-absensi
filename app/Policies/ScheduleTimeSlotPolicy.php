<?php

namespace App\Policies;

use App\Models\ScheduleTimeSlot;
use App\Models\User;

class ScheduleTimeSlotPolicy
{
    public function before(User $user): ?bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ScheduleTimeSlot $scheduleTimeSlot): bool
    {
        return false;
    }

    public function delete(User $user, ScheduleTimeSlot $scheduleTimeSlot): bool
    {
        return false;
    }
}
