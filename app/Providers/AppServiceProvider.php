<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

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
        // Share pending leaves with header component
        view()->composer('partials.admin.header', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                
                if (in_array($user->role, ['super_admin', 'hrd'])) {
                    // Pull leave requests if not pulled in the last 10 seconds
                    $cacheKey = 'last_leave_pull_time';
                    $lastPull = cache($cacheKey);
                    if (!$lastPull || (time() - $lastPull) > 10) {
                        try {
                            app(\App\Http\Controllers\LeaveApprovalController::class)->pullLeaveRequestsFromUnits();
                            cache([$cacheKey => time()]);
                        } catch (\Exception $e) {
                            // Ignore exception to prevent page crash on unit network error
                        }
                    }

                    $pendingLeavesQuery = \App\Models\LeaveRequest::where('status', 'Pending');
                    $pendingLeavesCount = (clone $pendingLeavesQuery)->count();
                    $pendingLeaves = $pendingLeavesQuery->latest()->limit(5)->get();
                    
                    $view->with(compact('pendingLeaves', 'pendingLeavesCount'));
                }
            }
        });
    }
}
