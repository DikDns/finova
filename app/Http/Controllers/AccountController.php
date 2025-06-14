<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $accounts = Account::where('budget_id', $budget->id)
            ->get();
        $accountTypes = $this->formatAccountTypes($accounts, $budget->id);

        $transactions = Transaction::where('budget_id', $budget->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->paginate(10);

        $categoryGroups = CategoryGroup::where('budget_id', $budget->id)
            ->with('categories')
            ->get();

        $categories = $categoryGroups->flatMap(function ($group) {
            return $group->categories;
        });

        return Inertia::render('app/Accounts', [
            'budget' => $budget,
            'account_types' => $accountTypes,
            'accounts' => $accounts,
            'categories' => $categories,
            'transactions' => $transactions
        ]);
    }

    public function show(Budget $budget, Account $account)
    {
        // Ensure the user can only view their own accounts
        if ($account->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $accountTypes = $this->formatAccountTypes($budget->accounts, $budget->id);

        $transactions = Transaction::where('budget_id', $budget->id)
            ->where('account_id', $account->id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->paginate(10);

        $categoryGroups = CategoryGroup::where('budget_id', $budget->id)
            ->with('categories')
            ->get();

        $categories = $categoryGroups->flatMap(function ($group) {
            return $group->categories;
        });

        return Inertia::render('app/Accounts', [
            'budget' => $budget,
            'account_types' => $accountTypes,
            'accounts' => $budget->accounts,
            'current_account' => $account,
            'transactions' => $transactions,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['cash', 'loan'])],
            'balance' => 'required|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
            'minimum_payment_monthly' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Create the account
            $account = Account::create([
                'budget_id' => $validated['budget_id'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'balance' => $validated['balance'],
                'interest' => $validated['interest'] ?? 0,
                'minimum_payment_monthly' => $validated['minimum_payment_monthly'] ?? 0
            ]);

            // For cash accounts, create an initial income transaction
            if ($validated['type'] === 'cash' && $validated['balance'] > 0) {
                Transaction::create([
                    'account_id' => $account->id,
                    'budget_id' => $validated['budget_id'],
                    'category_id' => null, // No category for initial balance
                    'payee' => 'Saldo Awal',
                    'date' => now(),
                    'amount' => $validated['balance'], // Positive amount for income
                    'memo' => 'Saldo awal rekening ' . $validated['name']
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Rekening berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat rekening: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function update(Request $request, Account $account)
    {
        // Ensure the user can only update their own accounts
        if ($account->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['cash', 'loan'])],
            'balance' => 'required|numeric|min:0',
            'interest' => 'nullable|numeric|min:0',
            'minimum_payment_monthly' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Store the old balance for comparison
            $oldBalance = $account->balance;
            $oldType = $account->type;

            // Update the account
            $account->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'balance' => $validated['balance'],
                'interest' => $validated['interest'] ?? 0,
                'minimum_payment_monthly' => $validated['minimum_payment_monthly'] ?? 0
            ]);

            // If balance changed and it's a cash account, create adjustment transaction
            if ($validated['type'] === 'cash' && $oldBalance != $validated['balance']) {
                $balanceDifference = $validated['balance'] - $oldBalance;

                Transaction::create([
                    'account_id' => $account->id,
                    'budget_id' => $account->budget_id,
                    'category_id' => null,
                    'payee' => 'Penyesuaian Saldo',
                    'date' => now(),
                    'amount' => $balanceDifference,
                    'memo' => 'Penyesuaian saldo rekening ' . $validated['name']
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Rekening berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal memperbarui rekening: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Account $account)
    {
        // Ensure the user can only delete their own accounts
        if ($account->budget->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Check if account has any transactions
            $transactionCount = Transaction::where('account_id', $account->id)->count();

            if ($transactionCount > 0) {
                throw new \Exception('Tidak dapat menghapus rekening yang memiliki transaksi.');
            }

            // Delete the account
            $account->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Rekening berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' =>  $e->getMessage()]);
        }
    }

    private function formatAccountTypes($accounts, $budgetId)
    {
        $groupedAccounts = $accounts->groupBy('type');
        $accountTypes = [];

        foreach ($groupedAccounts as $type => $typeAccounts) {
            $formattedAccounts = $typeAccounts->map(function ($account) use ($budgetId) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'url' => "/budgets/{$budgetId}/accounts/{$account->id}",
                    'balance' => (float) $account->balance
                ];
            })->toArray();


            $accountTypes[] = [
                'id' => $type,
                'type' => $type,
                'isActive' => true,
                'accounts' => $formattedAccounts
            ];
        }

        return $accountTypes;
    }
}
