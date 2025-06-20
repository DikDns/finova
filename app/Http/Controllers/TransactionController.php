<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\CategoryBudget;
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
            'type' => 'required|in:expense,income',
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
            $amount = abs($validated['amount']); // Selalu gunakan nilai positif
            $type = $validated['type'];

            // Jika tipe transaksi adalah expense, maka jumlah harus negatif
            if ($type === 'expense') {
                $amount = -$amount;

                // Cek apakah saldo cukup untuk transaksi expense
                if ($account->balance + $amount < 0) {
                    throw new \Exception('Saldo tidak cukup untuk melakukan transaksi ini.');
                }
            }

            // Jika ada current_account_id, berarti ini adalah transaksi pinjaman
            if ($current_account_id) {
                $current_account = Account::where('id', $current_account_id)
                    ->where('budget_id', $budget->id)
                    ->firstOrFail();

                // Cek apakah current_account adalah akun pinjaman (loan)
                if ($current_account->type === 'loan') {
                    // Pastikan amount selalu positif
                    $amount = abs($amount);

                    if ($type === 'expense') {
                        // Implementasi algoritma perhitungan bunga yang lebih tepat
                        $interest_rate = $current_account->interest; // Konversi persentase bunga ke desimal

                        // Hitung bunga harian berdasarkan saldo pinjaman
                        $daily_interest_rate = $interest_rate / 30; // Asumsi 30 hari dalam sebulan
                        $daily_interest = $current_account->balance * $daily_interest_rate;

                        // Hitung bunga terakumulasi untuk satu bulan (atau periode pembayaran)
                        // Ini bisa disesuaikan berdasarkan berapa hari sejak pembayaran terakhir
                        $days_since_last_payment = 30; // Default 30 hari (1 bulan)

                        // Cek transaksi terakhir untuk akun ini
                        $last_payment = Transaction::where('account_id', $current_account->id)
                            ->where('type', 'expense')
                            ->orderBy('date', 'desc')
                            ->first();

                        if ($last_payment) {
                            // Hitung hari sejak pembayaran terakhir
                            $last_payment_date = new \DateTime($last_payment->date);
                            $current_date = new \DateTime($validated['date']);
                            $interval = $last_payment_date->diff($current_date);
                            $days_since_last_payment = $interval->days;

                            // Batasi maksimum 60 hari untuk menghindari bunga yang terlalu besar
                            $days_since_last_payment = min($days_since_last_payment, 60);
                            // Minimal 1 hari
                            $days_since_last_payment = max($days_since_last_payment, 1);
                        }

                        // Hitung bunga berdasarkan jumlah hari
                        $interest_amount = $daily_interest * $days_since_last_payment;

                        // Batasi bunga maksimum sebesar 10% dari total pembayaran
                        $max_interest = $amount * 0.1;
                        $interest_amount = min($interest_amount, $max_interest);

                        // Total pembayaran tetap sama
                        $total_payment = $amount;
                        $principal_payment = $amount - $interest_amount;

                        // Jika pembayaran lebih kecil dari bunga yang dihitung, sesuaikan
                        if ($principal_payment < 0) {
                            $principal_payment = $amount * 0.8; // Minimal 80% pembayaran ke pokok
                            $interest_amount = $amount * 0.2; // Maksimal 20% pembayaran ke bunga
                        }
                        
                        // Jika pembayaran pokok melebihi saldo pinjaman, batasi pembayaran pokok
                        if ($principal_payment > $current_account->balance) {
                            // Simpan kelebihan pembayaran
                            $overpayment = $principal_payment - $current_account->balance;
                            
                            // Batasi pembayaran pokok maksimal sebesar saldo pinjaman
                            $principal_payment = $current_account->balance;
                            
                            // Tambahkan kelebihan pembayaran ke memo
                            $memo = empty($memo) ? "" : $memo . " ";
                            $memo .= "(Kelebihan pembayaran: " . number_format($overpayment, 2) . ")";
                        }
                        

                        // Pembayaran utang (expense): kurangi saldo pinjaman (utang berkurang) sebesar pembayaran pokok
                        $current_account->balance = $current_account->balance - $principal_payment;
                        $current_account->save();

                        // Kurangi saldo rekening sumber pembayaran sebesar total pembayaran (pokok + bunga)
                        $account->balance = $account->balance - $total_payment;
                        $account->save();

                        // Tambahkan informasi bunga ke memo jika tidak kosong
                        if (empty($memo)) {
                            $memo = "Pembayaran: Pokok " . number_format($principal_payment, 2) . ", Bunga " . number_format($interest_amount, 2);
                        } else {
                            $memo .= " (Pokok " . number_format($principal_payment, 2) . ", Bunga " . number_format($interest_amount, 2) . ")";
                        }
                    } else { // income
                        // Penambahan utang (income): tambah saldo pinjaman (utang bertambah)
                        $current_account->balance = $current_account->balance + $amount;
                        $current_account->save();

                        // Tambah saldo rekening tujuan
                        $account->balance = $account->balance + $amount;
                        $account->save();
                    }

                    // Buat transaksi dengan account_id adalah akun pinjaman
                    $transaction = Transaction::create([
                        'payee' => $account->id,
                        'memo' => $memo,
                        'amount' => $amount,
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id,
                        'budget_id' => $validated['budget_id'],
                        'type' => $type, // Gunakan tipe yang dipilih user (expense/income)
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
                        'type' => $type,
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
                    'type' => $type,
                ]);

                $account->balance = $account->balance + $amount;
                $account->save();
            }

            // Update category activity if category is set
            // Kondisi 1: Transaksi baru dengan kategori
            if (isset($validated['category_id']) && $validated['category_id'] !== null) {
                $this->updateCategoryActivity($validated['category_id'], $amount);
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
            'type' => 'required|in:expense,income',
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
            $oldCategoryId = $transaction->category_id;
            $newCategoryId = $validated['category_id'];
            $amount = abs($validated['amount']); // Selalu gunakan nilai positif
            $type = $validated['type'];

            // Jika tipe transaksi adalah expense, maka jumlah harus negatif
            if ($type === 'expense') {
                $amount = -$amount;

                // Cek apakah saldo cukup untuk transaksi expense
                // Hitung saldo setelah mengembalikan transaksi lama dan menambahkan yang baru
                $newBalance = $account->balance - $oldAmount + $amount;
                if ($newBalance < 0) {
                    throw new \Exception('Saldo tidak cukup untuk melakukan transaksi ini.');
                }
            }

            // Jika ada current_account_id, berarti ini adalah transaksi pinjaman
            if ($current_account_id) {
                $current_account = Account::where('id', $current_account_id)
                    ->where('budget_id', $budget->id)
                    ->firstOrFail();

                // Cek apakah current_account adalah akun pinjaman (loan)
                if ($current_account->type === 'loan') {
                    // Pastikan amount selalu positif
                    $amount = abs($amount);
                    $oldType = $transaction->type;

                    // Kembalikan saldo lama pada akun pinjaman berdasarkan tipe transaksi lama
                    if ($oldType === 'expense') {
                        // Jika transaksi lama adalah pembayaran utang, kembalikan saldo pinjaman (utang bertambah)
                        $current_account->balance = $current_account->balance + $oldAmount;

                        // Kembalikan saldo lama pada akun sumber pembayaran lama (disimpan di payee)
                        $oldSourceAccount = Account::findOrFail($transaction->payee);
                        $oldSourceAccount->balance = $oldSourceAccount->balance + $oldAmount;
                        $oldSourceAccount->save();
                    } else { // income
                        // Jika transaksi lama adalah penambahan utang, kurangi saldo pinjaman (utang berkurang)
                        $current_account->balance = $current_account->balance - $oldAmount;

                        // Kembalikan saldo lama pada akun tujuan lama (disimpan di payee)
                        $oldSourceAccount = Account::findOrFail($transaction->payee);
                        $oldSourceAccount->balance = $oldSourceAccount->balance - $oldAmount;
                        $oldSourceAccount->save();
                    }

                    // Terapkan transaksi baru berdasarkan tipe baru
                    if ($type === 'expense') {
                        // Implementasi algoritma perhitungan bunga yang lebih tepat
                        $interest_rate = $current_account->interest;

                        // Hitung bunga harian berdasarkan saldo pinjaman
                        $daily_interest_rate = $interest_rate / 30; // Asumsi 30 hari dalam sebulan
                        $daily_interest = $current_account->balance * $daily_interest_rate;

                        // Hitung bunga terakumulasi untuk satu bulan (atau periode pembayaran)
                        // Ini bisa disesuaikan berdasarkan berapa hari sejak pembayaran terakhir
                        $days_since_last_payment = 30; // Default 30 hari (1 bulan)

                        // Cek transaksi terakhir untuk akun ini (selain transaksi yang sedang diupdate)
                        $last_payment = Transaction::where('account_id', $current_account->id)
                            ->where('type', 'expense')
                            ->where('id', '!=', $transaction->id)
                            ->orderBy('date', 'desc')
                            ->first();

                        if ($last_payment) {
                            // Hitung hari sejak pembayaran terakhir
                            $last_payment_date = new \DateTime($last_payment->date);
                            $current_date = new \DateTime($validated['date']);
                            $interval = $last_payment_date->diff($current_date);
                            $days_since_last_payment = $interval->days;

                            // Batasi maksimum 60 hari untuk menghindari bunga yang terlalu besar
                            $days_since_last_payment = min($days_since_last_payment, 60);
                            // Minimal 1 hari
                            $days_since_last_payment = max($days_since_last_payment, 1);
                        }

                        // Hitung bunga berdasarkan jumlah hari
                        $interest_amount = $daily_interest * $days_since_last_payment;

                        // Batasi bunga maksimum sebesar 10% dari total pembayaran
                        $max_interest = $amount * 0.1;
                        $interest_amount = min($interest_amount, $max_interest);

                        // Total pembayaran tetap sama
                        $total_payment = $amount;
                        $principal_payment = $amount - $interest_amount;

                        // Jika pembayaran lebih kecil dari bunga yang dihitung, sesuaikan
                        if ($principal_payment < 0) {
                            $principal_payment = $amount * 0.8; // Minimal 80% pembayaran ke pokok
                            $interest_amount = $amount * 0.2; // Maksimal 20% pembayaran ke bunga
                        }
                        
                        // Jika pembayaran pokok melebihi saldo pinjaman, batasi pembayaran pokok
                        if ($principal_payment > $current_account->balance) {
                            // Simpan kelebihan pembayaran
                            $overpayment = $principal_payment - $current_account->balance;
                            
                            // Batasi pembayaran pokok maksimal sebesar saldo pinjaman
                            $principal_payment = $current_account->balance;
                            
                            // Tambahkan kelebihan pembayaran ke memo
                            $memo = empty($memo) ? "" : $memo . " ";
                            $memo .= "(Kelebihan pembayaran: " . number_format($overpayment, 2) . ")";
                        }

                        // Pembayaran utang (expense): kurangi saldo pinjaman (utang berkurang) sebesar pembayaran pokok
                        $current_account->balance = $current_account->balance - $principal_payment;
                        $current_account->save();

                        // Kurangi saldo rekening sumber pembayaran baru sebesar total pembayaran (pokok + bunga)
                        $newSourceAccount = Account::findOrFail($payee);
                        $newSourceAccount->balance = $newSourceAccount->balance - $total_payment;
                        $newSourceAccount->save();

                        // Tambahkan informasi bunga ke memo jika tidak kosong
                        if (empty($memo)) {
                            $memo = "Pembayaran: Pokok " . number_format($principal_payment, 2) . ", Bunga " . number_format($interest_amount, 2);
                        } else {
                            $memo .= " (Pokok " . number_format($principal_payment, 2) . ", Bunga " . number_format($interest_amount, 2) . ")";
                        }
                    } else { // income
                        // Penambahan utang (income): tambah saldo pinjaman (utang bertambah)
                        $current_account->balance = $current_account->balance + $amount;
                        $current_account->save();

                        // Tambah saldo rekening tujuan baru
                        $newSourceAccount = Account::findOrFail($payee);
                        $newSourceAccount->balance = $newSourceAccount->balance + $amount;
                        $newSourceAccount->save();
                    }

                    // Update transaksi
                    // payee: sumber rekening baru, tetap disimpan di payee
                    // account_id: rekening type 'loan' disimpan ke account_id
                    $transaction->update([
                        'payee' => $payee, // sumber rekening baru
                        'memo' => $memo,
                        'amount' => $amount,
                        'date' => $validated['date'],
                        'category_id' => $validated['category_id'],
                        'account_id' => $current_account_id, // rekening loan
                        'type' => $type, // Gunakan tipe yang dipilih user (expense/income)
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
                        'type' => $type,
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
                    'type' => $type,
                ]);

                // Handle category activity updates
                // Dapatkan kategori ID asli sebelum update

                // Kondisi 1: Transaksi diubah jumlahnya namun tetap sama kategori id
                if ($oldCategoryId === $newCategoryId && $oldCategoryId !== null) {
                    // Hitung selisih amount dan update activity
                    $amountDifference = $amount - $oldAmount;
                    $this->updateCategoryActivity($oldCategoryId, $amountDifference);
                }
                // Kondisi 2: Transaksi tidak diubah jumlahnya namun beda kategori id
                // atau transaksi diubah jumlahnya dan beda kategori id
                else {
                    // Hapus amount dari kategori lama jika ada
                    if ($oldCategoryId) {
                        $this->updateCategoryActivity($oldCategoryId, -$oldAmount);
                    }

                    // Tambahkan amount ke kategori baru jika ada
                    if ($newCategoryId) {
                        $this->updateCategoryActivity($newCategoryId, $amount);
                    }
                }
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
                // Ambil jumlah transaksi (selalu positif)
                $amount = abs($transaction->amount);

                if ($transaction->type === 'expense') {
                    // Jika transaksi adalah pembayaran utang (expense), kita perlu menghitung kembali
                    // berapa bagian pokok dan bunga dari pembayaran tersebut

                    // Coba ekstrak informasi pembayaran pokok dari memo transaksi
                    $principal_payment = null;
                    $interest_amount = null;

                    // Coba cari informasi pembayaran pokok dan bunga dari memo
                    if (preg_match('/Pembayaran Pokok: ([\d.,]+)/', $transaction->memo, $matches)) {
                        $principal_payment = (float) str_replace([',', '.'], '.', $matches[1]);

                        if (preg_match('/Bunga: ([\d.,]+)/', $transaction->memo, $matches)) {
                            $interest_amount = (float) str_replace([',', '.'], '.', $matches[1]);
                        }
                    }

                    // Jika tidak bisa mengekstrak dari memo, hitung ulang
                    if ($principal_payment === null || $interest_amount === null) {
                        // Hitung ulang berdasarkan algoritma yang sama dengan store/update
                        $interest_rate = $account->interest / 100; // Konversi persentase bunga ke desimal

                        // Hitung bunga harian
                        $daily_interest_rate = $interest_rate / 30;
                        $daily_interest = ($account->balance - $amount) * $daily_interest_rate; // Gunakan saldo sebelum pembayaran

                        // Asumsikan pembayaran untuk 30 hari bunga
                        $days_payment = 30;

                        // Batasi bunga maksimum sebesar 10% dari total pembayaran
                        $interest_amount = min($daily_interest * $days_payment, $amount * 0.1);

                        // Pastikan minimal 80% pembayaran ke pokok jika bunga terlalu besar
                        if (($amount - $interest_amount) < ($amount * 0.8)) {
                            $interest_amount = $amount * 0.2;
                        }

                        $principal_payment = $amount - $interest_amount;
                    }

                    // Kembalikan hanya pembayaran pokok ke saldo pinjaman (utang bertambah)
                    $account->balance = $account->balance + $principal_payment;
                    $account->save();

                    // Kembalikan seluruh jumlah pembayaran ke rekening sumber pembayaran
                    $sourceAccount = Account::find($transaction->payee);
                    if ($sourceAccount) {
                        $sourceAccount->balance = $sourceAccount->balance + $amount; // Kembalikan total pembayaran
                        $sourceAccount->save();
                    }
                } else { // income
                    // Jika transaksi adalah penambahan utang (income), kurangi saldo pinjaman (utang berkurang)
                    $account->balance = $account->balance - $amount;
                    $account->save();

                    // Kembalikan saldo rekening tujuan (disimpan di payee)
                    $sourceAccount = Account::find($transaction->payee);
                    if ($sourceAccount) {
                        $sourceAccount->balance = $sourceAccount->balance - $amount;
                        $sourceAccount->save();
                    }
                }
            } else {
                // Untuk akun normal, kurangi saldo sesuai jumlah transaksi
                $account->balance = $account->balance - $amount;
                $account->save();
            }

            // Kondisi 3: Ketika transaksi dihapus
            // Update category activity when deleting transaction
            if ($transaction->category_id !== null) {
                $this->updateCategoryActivity($transaction->category_id, -$amount);
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

    private function authorizeTransaction(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->account->budget->user_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses transaksi ini.');
        }
    }

    private function updateCategoryActivity($categoryId, $amount)
    {
        $categoryBudgets = CategoryBudget::where('category_id', $categoryId)
            ->get();

        foreach ($categoryBudgets as $categoryBudget) {
            $categoryBudget->activity = $categoryBudget->activity + $amount;
            $categoryBudget->save();
        }
    }
}
