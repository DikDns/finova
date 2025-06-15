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
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payee' => 'nullable|string|max:255',
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

            $payee = $validated['payee'] ?? '';
            $memo = $validated['memo'] ?? '';

            // Create the transaction
            $transaction = Transaction::create([
                'payee' => $payee,
                'memo' => $memo,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'category_id' => $validated['category_id'],
                'account_id' => $validated['account_id'],
                'budget_id' => $validated['budget_id'],
            ]);

            // Update account balance
            $account->balance = $account->balance + $validated['amount'];
            $account->save();

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
            'payee' => 'nullable|string|max:255',
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

            $payee = $validated['payee'] ?? '';
            $memo = $validated['memo'] ?? '';

            // Get the old transaction data to calculate balance adjustment
            $oldAmount = $transaction->amount;
            $oldAccountId = $transaction->account_id;

            // If account changed, adjust both old and new account balances
            if ($oldAccountId !== $validated['account_id']) {
                // Subtract amount from old account
                $oldAccount = Account::findOrFail($oldAccountId);
                $oldAccount->balance = $oldAccount->balance - $oldAmount;
                $oldAccount->save();

                // Add amount to new account
                $account->balance = $account->balance + $validated['amount'];
                $account->save();
            } else {
                // Same account, just adjust the difference
                $account->balance = $account->balance - $oldAmount + $validated['amount'];
                $account->save();
            }

            // Update the transaction
            $transaction->update([
                'payee' => $payee,
                'memo' => $memo,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'category_id' => $validated['category_id'],
                'account_id' => $validated['account_id'],
            ]);

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

            // Get transaction details before deletion
            $amount = $transaction->amount;
            $accountId = $transaction->account_id;

            // Update account balance
            $account = Account::findOrFail($accountId);
            $account->balance = $account->balance - $amount;
            $account->save();

            // Delete the transaction
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
