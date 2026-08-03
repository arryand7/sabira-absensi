<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Schedule;
use App\Models\ScheduleConflict;
use App\Models\ScheduleTimeSlot;
use App\Policies\ScheduleConflictPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ScheduleTimeSlotPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Schedule::class => SchedulePolicy::class,
        ScheduleConflict::class => ScheduleConflictPolicy::class,
        ScheduleTimeSlot::class => ScheduleTimeSlotPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
