<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLog;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = UserLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                               ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $userLogs = $query->paginate($perPage);

        // Transform data untuk frontend
        $transformedLogs = $userLogs->getCollection()->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? $log->user->email : 'Unknown User',
                'user_name' => $log->user ? $log->user->name : 'Unknown',
                'action' => $log->action,
                'description' => $log->description,
                'ipAddress' => $log->ip_address,
                'userAgent' => $log->user_agent,
                'oldValues' => $log->old_values,
                'newValues' => $log->new_values,
                'createdAt' => $log->created_at->format('Y-m-d H:i:s'),
                'updatedAt' => $log->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Hitung total users dan subscriptions dari database
        $totalUsers = User::count();
        $totalSubscriptions = Subscription::count();

        // Hitung pertumbuhan dari bulan sebelumnya
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Users bulan lalu
        $usersLastMonth = User::where('created_at', '<=', $lastMonthEnd)->count();
        $usersGrowth = $usersLastMonth > 0 ? round((($totalUsers - $usersLastMonth) / $usersLastMonth) * 100, 1) : 0;

        // Subscriptions bulan lalu
        $subscriptionsLastMonth = Subscription::where('created_at', '<=', $lastMonthEnd)->count();
        $subscriptionsGrowth = $subscriptionsLastMonth > 0 ? round((($totalSubscriptions - $subscriptionsLastMonth) / $subscriptionsLastMonth) * 100, 1) : 0;

        return Inertia::render('admin/AdminDashboard', [
            'userLogs' => [
                'data' => $transformedLogs,
                'current_page' => $userLogs->currentPage(),
                'last_page' => $userLogs->lastPage(),
                'per_page' => $userLogs->perPage(),
                'total' => $userLogs->total(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'totalUsers' => $totalUsers,
            'totalSubscriptions' => $totalSubscriptions,
            'usersGrowth' => $usersGrowth,
            'subscriptionsGrowth' => $subscriptionsGrowth,
        ]);
    }
}