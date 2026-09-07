<?php

namespace App\Providers;

use App\Models\OptimizationFeedback;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        if (config('database.default') === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && $dbPath !== ':memory:') {
                if (! file_exists($dbPath)) {
                    $dir = dirname($dbPath);
                    if (! is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    touch($dbPath);
                }
                try {
                    if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
                        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                    }
                } catch (\Throwable $e) {
                    // Exception swallowed to prevent crash during initial boot
                }
            }
        }

        View::composer('components.layouts.app', function ($view): void {
            $user = auth()->user();
            $pendingFeedbackCount = $user
                ? OptimizationFeedback::query()
                    ->where('status', '!=', 'resolved')
                    ->when(! $user->hasRole('administrator'), fn ($query) => $query->where('target_stage', $user->department?->code))
                    ->count()
                : 0;

            $view->with('pendingFeedbackCount', $pendingFeedbackCount);
        });
    }
}
