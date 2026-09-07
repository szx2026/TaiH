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
