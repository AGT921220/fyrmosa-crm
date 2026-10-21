<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role as SpatieRole;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (mixed $user): ?bool {
            if (! $user instanceof User) {
                return null;
            }

            if ($user->role !== Role::SuperAdmin->value) {
                return null;
            }

            return true;
        });

        Gate::policy(SpatieRole::class, RolePolicy::class);

        Relation::enforceMorphMap([
            'quotation' => Quotation::class,
            'order' => Order::class,
            'user' => User::class,
        ]);

        Paginator::useBootstrapFive();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
