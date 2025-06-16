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
            'current_account_id' => 'nullable|exists:accounts,id',
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
            $current_account_id = $validated['current_account_id'] ?? null;
            $amount = $validated['amount'];

            // Jika ada current_account_id, berarti ini adalah transaksi pinjaman
            if ($current_account_id) {
                $current_account = Account::where('id', $current_account_id)
                    ->where('budget_id', $budget->id)
                    ->firstOrFail();

                // Cek apakah current_account adalah akun pinjaman (loan)
                if ($current_account->type === 'loan') {
                    // Pastikan amount selalu positif untuk pembayaran pinjaman
                    $amount = abs($amount);

                    // Kurangi saldo pinjaman (balance berkurang karena utang berkurang)
                    $current_account->balance = $current_account->balance - $amount;
                    $current_account->save();

                    // Kurangi saldo rekening sumber pembayaran
                    $account->balance = $account->balance - $amount;
                    $account->save();

                    // Buat transaksi dengan account_id adalah akun pinjaman
                    $transaction = Transaction::create([
                        'payee' => $account->id,
                        'memo' => $memo,
                        'amount' => $amount, // Selalu positif untuk pembayaran pinjaman
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id,
                        'budget_id' => $validated['budget_id'],
                    ]);
                } else {
                    // Jika bukan akun pinjaman, gunakan logika normal
                    $transaction = Transaction::create([
                        'payee' => $payee,
                        'memo' => $memo,
                        'amount' => $amount,
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id,
                        'budget_id' => $validated['budget_id'],
                    ]);

                    $current_account->balance = $current_account->balance + $amount;
                    $current_account->save();
                }
            } else {
                // Transaksi normal tanpa current_account_id
                $transaction = Transaction::create([
                    'payee' => $payee,
                    'memo' => $memo,
                    'amount' => $amount,
                    'date' => $validated['date'],
                    'category_id' => $validated['category_id'],
                    'account_id' => $validated['account_id'],
                    'budget_id' => $validated['budget_id'],
                ]);

                $account->balance = $account->balance + $amount;
                $account->save();
            }

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
            'current_account_id' => 'nullable|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify the new account belongs to the specified budget and user has access
            $user = $request->user();
            $budget = $user->budgets()->findOrFail($validated['budget_id']);
            $account = Account::findOrFail($validated['account_id']);

            $payee = $validated['payee'] ?? '';
            $memo = $validated['memo'] ?? '';
            $current_account_id = $validated['current_account_id'] ?? null;

            // Get the old transaction data to calculate balance adjustment
            $oldAmount = $transaction->amount;
            $oldAccountId = $transaction->account_id;
            $amount = $validated['amount'];

            // Jika ada current_account_id, berarti ini adalah transaksi pinjaman
            if ($current_account_id) {
                $current_account = Account::where('id', $current_account_id)
                    ->where('budget_id', $budget->id)
                    ->firstOrFail();

                // Cek apakah current_account adalah akun pinjaman (loan)
                if ($current_account->type === 'loan') {
                    // Pastikan amount selalu positif untuk pembayaran pinjaman
                    $amount = abs($amount);

                    // Kembalikan saldo lama pada akun pinjaman
                    $current_account->balance = $current_account->balance + $oldAmount;

                    // Kembalikan saldo lama pada akun sumber pembayaran lama (disimpan di payee)
                    $oldSourceAccount = Account::findOrFail($account->id);
                    $oldSourceAccount->balance = $oldSourceAccount->balance + $oldAmount;
                    $oldSourceAccount->save();

                    // Kurangi saldo pinjaman dengan jumlah baru
                    $current_account->balance = $current_account->balance - $amount;
                    $current_account->save();

                    // Kurangi saldo rekening sumber pembayaran baru dengan jumlah baru
                    $newSourceAccount = Account::findOrFail($payee);
                    $newSourceAccount->balance = $newSourceAccount->balance - $amount;
                    $newSourceAccount->save();

                    // Update transaksi
                    // payee: sumber rekening baru, tetap disimpan di payee
                    // account_id: rekening type 'loan' disimpan ke account_id
                    $transaction->update([
                        'payee' => $payee, // sumber rekening baru
                        'memo' => $memo,
                        'amount' => $amount, // Selalu positif untuk pembayaran pinjaman
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id, // rekening loan
                    ]);
                } else {
                    // Jika bukan akun pinjaman, gunakan logika normal
                    // If account changed, adjust both old and new account balances
                    if ($oldAccountId !== $current_account_id) {
                        // Subtract amount from old account
                        $oldAccount = Account::findOrFail($oldAccountId);
                        $oldAccount->balance = $oldAccount->balance - $oldAmount;
                        $oldAccount->save();

                        // Add amount to new account
                        $current_account->balance = $current_account->balance + $amount;
                        $current_account->save();
                    } else {
                        // Same account, just adjust the difference
                        $current_account->balance = $current_account->balance - $oldAmount + $amount;
                        $current_account->save();
                    }

                    // Update transaksi
                    $transaction->update([
                        'payee' => $payee,
                        'memo' => $memo,
                        'amount' => $amount,
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id,
                    ]);
                }
            } else {
                // Transaksi normal tanpa current_account_id
                // If account changed, adjust both old and new account balances
                if ($oldAccountId !== $validated['account_id']) {
                    // Subtract amount from old account
                    $oldAccount = Account::findOrFail($oldAccountId);
                    $oldAccount->balance = $oldAccount->balance - $oldAmount;
                    $oldAccount->save();

                    // Add amount to new account
                    $account->balance = $account->balance + $amount;
                    $account->save();
                } else {
                    // Same account, just adjust the difference
                    $account->balance = $account->balance - $oldAmount + $amount;
                    $account->save();
                }

                // Update transaksi
                $transaction->update([
                    'payee' => $payee,
                    'memo' => $memo,
                    'amount' => $amount,
                    'date' => $validated['date'],
                    'category_id' => $validated['category_id'],
                    'account_id' => $validated['account_id'],
                ]);
            }

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
            $account = Account::findOrFail($accountId);

            // Cek apakah ini transaksi akun pinjaman
            if ($account->type === 'loan') {
                // Untuk akun pinjaman, tambahkan kembali saldo pinjaman (utang bertambah)
                $account->balance = $account->balance + $amount;
                $account->save();

                // Untuk transaksi pinjaman, payee menyimpan id akun sumber pembayaran
                // Kembalikan saldo ke akun sumber pembayaran
                if ($transaction->payee) {
                    $sourceAccount = Account::find($transaction->payee);
                    if ($sourceAccount) {
                        $sourceAccount->balance = $sourceAccount->balance + $amount;
                        $sourceAccount->save();
                    }
                }
            } else {
                // Untuk akun normal, kurangi saldo sesuai jumlah transaksi
                $account->balance = $account->balance - $amount;
                $account->save();
            }

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
