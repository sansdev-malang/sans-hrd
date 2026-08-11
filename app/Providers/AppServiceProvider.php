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

                    $cookieName = 'read_leave_ids_' . $user->id;
                    $readIds = request()->cookie($cookieName);
                    $readIds = $readIds ? json_decode($readIds, true) : [];
                    if (!is_array($readIds)) {
                        $readIds = [];
                    }
                    if (request()->has('read_id')) {
                        $readIds[] = (int) request()->input('read_id');
                    }
                    if (request()->has('clear_all')) {
                        $recentIds = \App\Models\LeaveRequest::where('created_at', '>=', now()->subDays(3))->pluck('id')->toArray();
                        $readIds = array_merge($readIds, $recentIds);
                    }
                    $pendingLeavesQuery = \App\Models\LeaveRequest::where('created_at', '>=', now()->subDays(3))
                        ->whereNotIn('id', $readIds);
                    $pendingLeavesCount = (clone $pendingLeavesQuery)->count();
                    $pendingLeaves = $pendingLeavesQuery->latest()->limit(5)->get();
                    
                    $view->with(compact('pendingLeaves', 'pendingLeavesCount'));
                }
            }
        });
    }
}
