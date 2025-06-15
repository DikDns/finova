<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionLogController extends Controller
{
    public function index(Request $request) {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Subscription::with('user')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhere('start_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $subscriptions = $query->paginate($perPage);

        // Transform data untuk frontend
        $transformedSubscriptions = $subscriptions->getCollection()->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'user' => $subscription->user ? $subscription->user->email : 'Unknown User',
                'user_name' => $subscription->user ? $subscription->user->name : 'Unknown',
                'invoice' => $subscription->invoice,
                'payment_method' => $subscription->payment_method,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'createdAt' => $subscription->created_at->format('Y-m-d H:i:s'),
                'updatedAt' => $subscription->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('admin/SubscriptionLog', [
            'subscriptionLogs' => [
                'data' => $transformedSubscriptions,
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ]
        ]);
    }
}
