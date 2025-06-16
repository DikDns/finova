<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $type = $request->get('type');

        $query = Account::with(['budget.user'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('budget', function ($budgetQuery) use ($search) {
                      $budgetQuery->where('name', 'like', "%{$search}%")
                                 ->orWhereHas('user', function ($userQuery) use ($search) {
                                     $userQuery->where('name', 'like', "%{$search}%")
                                              ->orWhere('email', 'like', "%{$search}%");
                                 });
                  });
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $accounts = $query->paginate($perPage);

        $transformedAccounts = $accounts->getCollection()->map(function ($account) {
            return [
                'id' => (string) $account->id,
                'budget_id' => (string) $account->budget_id,
                'budget_name' => $account->budget ? $account->budget->name : 'Unknown Budget',
                'user_name' => $account->budget && $account->budget->user ? $account->budget->user->name : 'Unknown User',
                'user_email' => $account->budget && $account->budget->user ? $account->budget->user->email : 'Unknown Email',
                'name' => $account->name,
                'type' => $account->type,
                'interest' => (float) $account->interest,
                'minimum_payment_monthly' => (float) $account->minimum_payment_monthly,
                'balance' => (float) $account->balance,
                'createdAt' => $account->created_at ? $account->created_at->format('Y-m-d H:i:s') : '',
                'updatedAt' => $account->updated_at ? $account->updated_at->format('Y-m-d H:i:s') : '',
            ];
        });

        $totalAccounts = Account::count();
        $totalBalance = Account::sum('balance') ?: 0;
        $averageBalance = $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0;

        $accountsByType = [
            'cash' => Account::where('type', 'cash')->count(),
            'savings' => Account::where('type', 'savings')->count(),
            'credit' => Account::where('type', 'like', '%credit%')->count(),
            'investment' => Account::where('type', 'investment')->count(),
        ];

        return Inertia::render('admin/AdminAccount', [
            'accounts' => [
                'data' => $transformedAccounts,
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
            'filters' => [
                'search' => $search,
                'per_page' => (int) $perPage,
                'type' => $type,
            ],
            'totalAccounts' => $totalAccounts,
            'totalBalance' => $totalBalance,
            'averageBalance' => $averageBalance,
            'accountsByType' => $accountsByType,
        ]);
    }

    public function show($id)
    {
        $account = Account::with(['budget.user'])->findOrFail($id);

        $accountData = [
            'id' => (string) $account->id,
            'budget_id' => (string) $account->budget_id,
            'budget_name' => $account->budget ? $account->budget->name : 'Unknown Budget',
            'user_name' => $account->budget && $account->budget->user ? $account->budget->user->name : 'Unknown User',
            'user_email' => $account->budget && $account->budget->user ? $account->budget->user->email : 'Unknown Email',
            'name' => $account->name,
            'type' => $account->type,
            'interest' => (float) $account->interest,
            'minimum_payment_monthly' => (float) $account->minimum_payment_monthly,
            'balance' => (float) $account->balance,
            'createdAt' => $account->created_at ? $account->created_at->format('Y-m-d H:i:s') : '',
            'updatedAt' => $account->updated_at ? $account->updated_at->format('Y-m-d H:i:s') : '',
        ];

        return response()->json([
            'account' => $accountData
        ]);
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        return redirect()->back()->with('success', 'Account deleted successfully.');
    }

    public function getAccountStats()
    {
        $totalAccounts = Account::count();
        $totalBalance = Account::sum('balance') ?: 0;
        $averageBalance = $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0;

        $accountsByType = [
            'cash' => Account::where('type', 'cash')->count(),
            'savings' => Account::where('type', 'savings')->count(),
            'credit' => Account::where('type', 'like', '%credit%')->count(),
            'investment' => Account::where('type', 'investment')->count(),
        ];

        $balanceByType = [
            'cash' => Account::where('type', 'cash')->sum('balance') ?: 0,
            'savings' => Account::where('type', 'savings')->sum('balance') ?: 0,
            'credit' => Account::where('type', 'like', '%credit%')->sum('balance') ?: 0,
            'investment' => Account::where('type', 'investment')->sum('balance') ?: 0,
        ];

        $topAccounts = Account::with(['budget.user'])
            ->orderBy('balance', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => (string) $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => (float) $account->balance,
                    'user_name' => $account->budget && $account->budget->user ? $account->budget->user->name : 'Unknown User',
                    'budget_name' => $account->budget ? $account->budget->name : 'Unknown Budget',
                ];
            });

        return response()->json([
            'totalAccounts' => $totalAccounts,
            'totalBalance' => $totalBalance,
            'averageBalance' => $averageBalance,
            'accountsByType' => $accountsByType,
            'balanceByType' => $balanceByType,
            'topAccounts' => $topAccounts,
        ]);
    }
}