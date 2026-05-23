<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Models\Transaction;
use App\Observers\AssetObserver;
use App\Observers\MaintenanceLogObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Asset::observe(AssetObserver::class);
        Transaction::observe(TransactionObserver::class);
        MaintenanceLog::observe(MaintenanceLogObserver::class);

        Role::saving(function (Role $role) {
            if (empty($role->guard_name)) {
                $role->guard_name = 'web';
            }
        });

        DB::listen(function ($query) {
             if ($query->time > 100) { // log queries slower than 100ms
                 Log::info("Slow Query ({$query->time}ms): {$query->sql} with bindings: " . json_encode($query->bindings));
             }
        });
    }
}
