<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Get authenticated user
        $user = $request->user();

        // Get user's transactions through the relationship chain: user -> budgets -> accounts -> transactions
        $userBudgetIds = $user->budgets()->pluck('id');
        $userAccountIds = Account::whereIn('budget_id', $userBudgetIds)->pluck('id');

        $transactions = Transaction::whereIn('account_id', $userAccountIds)
            ->with(['category', 'account'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        return Inertia::render('users/Transactions', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('users/TransactionForm');
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Verify the account belongs to one of the user's budgets
        $user = $request->user();
        $userBudgetIds = $user->budgets()->pluck('id');
        $account = Account::findOrFail($validated['account_id']);

        if (!in_array($account->budget_id, $userBudgetIds->toArray())) {
            abort(403, 'You do not have permission to create transactions for this account.');
        }

        Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }


    /**
     * Show the form for editing the specified transaction.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Inertia\Response
     */
    public function edit(Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated user through account -> budget -> user
        $this->authorizeTransaction($transaction);

        return Inertia::render('users/TransactionForm', [
            'transaction' => $transaction->load(['category', 'account']),
        ]);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated user through account -> budget -> user
        $this->authorizeTransaction($transaction);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Verify the new account belongs to one of the user's budgets
        $user = $request->user();
        $userBudgetIds = $user->budgets()->pluck('id');
        $account = Account::findOrFail($validated['account_id']);

        if (!in_array($account->budget_id, $userBudgetIds->toArray())) {
            abort(403, 'You do not have permission to use this account.');
        }

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated user through account -> budget -> user
        $this->authorizeTransaction($transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Authorize that the transaction belongs to the authenticated user.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    private function authorizeTransaction(Transaction $transaction)
    {
        $currentUser = auth()->guard()->user();

        if (!$currentUser || $currentUser->id !== $transaction->account->budget->user_id) {
            abort(403, 'You do not have permission to access this transaction.');
        }
    }
}
