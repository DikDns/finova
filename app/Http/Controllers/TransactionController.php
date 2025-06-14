<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'memo' => 'nullable|string|max:500',
            'budget_id' => 'required|exists:budgets,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify the account belongs to the specified budget and user has access
            $user = $request->user();
            $budget = $user->budgets()->findOrFail($validated['budget_id']);
            $account = Account::where('id', $validated['account_id'])
                ->where('budget_id', $budget->id)
                ->firstOrFail();

           

            Transaction::create($validated);

            DB::commit();

            return back()->with('success', 'Transaksi berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal membuat transaksi: ' . $e->getMessage()])
                ->withInput();
        }
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
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
            'memo' => 'nullable|string|max:500',
            'budget_id' => 'required|exists:budgets,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify the new account belongs to the specified budget and user has access
            $user = $request->user();
            $budget = $user->budgets()->findOrFail($validated['budget_id']);
            $account = Account::where('id', $validated['account_id'])
                ->where('budget_id', $budget->id)
                ->firstOrFail();


            $transaction->update($validated);

            DB::commit();

            return back()->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal memperbarui transaksi: ' . $e->getMessage()])
                ->withInput();
        }
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

        try {
            DB::beginTransaction();

            $transaction->delete();

            DB::commit();

            return back()->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Authorize that the transaction belongs to the authenticated user.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    private function authorizeTransaction(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->account->budget->user_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses transaksi ini.');
        }
    }
}
