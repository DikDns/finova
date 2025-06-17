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
            ->whereHas('account', function ($query) {
                $query->where('type', '!=', 'loan');
            })
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
            'accounts' => $this->getCashAccounts($budget->id),
            'categories' => $categories,
            'transactions' => $transactions,
            'current_account' => [
                'balance' => $this->getCurrentAllCashAccountsBalance($budget->id),
            ]
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


        if ($account->type === 'loan') {
            // Dapatkan prediksi pelunasan utang
            $loanPredictions = $this->getLoanAccountProgressPrediction($account);

            return Inertia::render('app/LoanAccount', [
                'budget' => $budget,
                'account_types' => $accountTypes,
                'accounts' => $this->getCashAccounts($budget->id),
                'current_account' => $account,
                'transactions' => $transactions,
                'categories' => $categories,
                'loan_predictions' => $loanPredictions,
            ]);
        }

        return Inertia::render('app/Accounts', [
            'budget' => $budget,
            'account_types' => $accountTypes,
            'accounts' => $this->getCashAccounts($budget->id),
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

            return redirect()->route('budget.accounts.show', [$account->budget->id, $account->id])
                ->with('success', 'Rekening berhasil dibuat');
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
                'balance' =>  $validated['balance'],
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

            return redirect()->route('budget', $account->budget_id)->with('success', 'Rekening berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' =>  $e->getMessage()]);
        }
    }

    private function getLoanAccountProgressPrediction(Account $account)
    {
        // Jika tidak ada saldo utang, kembalikan array kosong
        if ($account->balance <= 0 || $account->type !== 'loan') {
            return [];
        }

        $currentBalance = (float) $account->balance;
        $interestRate = (float) $account->interest;
        $minimumPayment = (float) $account->minimum_payment_monthly;

        // Jika tidak ada pembayaran minimum, gunakan 5% dari saldo sebagai default
        if ($minimumPayment <= 0) {
            $minimumPayment = $currentBalance * 0.05;
        }

        $predictions = [];
        $currentDate = now();
        // Konversi persentase bunga bulanan ke desimal
        $monthlyInterestRate = ($interestRate / 100);

        // Hitung bunga bulanan awal
        $initialInterest = $currentBalance * $monthlyInterestRate;

        // Tambahkan data awal
        $predictions[] = [
            'date' => $currentDate->format('Y-m-d'),
            'balance' => round($currentBalance, 2),
            'monthly_interest' => round($initialInterest, 2), // Bunga bulanan awal
        ];

        // Hitung prediksi untuk 24 bulan ke depan
        for ($i = 1; $i <= 24; $i++) {
            // Tambahkan bunga bulanan
            $interest = $currentBalance * $monthlyInterestRate;

            // Pastikan pembayaran minimum lebih besar dari bunga agar saldo selalu menurun
            // Pembayaran efektif termasuk bunga dan pokok pinjaman
            // Perhitungan pembayaran yang menurun seiring dengan penurunan saldo utang
            $principalPayment = $currentBalance * 0.05; // 5% dari saldo utang saat ini
            $effectivePayment = max($minimumPayment, $interest + $principalPayment);

            // Kurangi saldo dengan pembayaran efektif (setelah dikurangi bunga)
            $currentBalance = $currentBalance + $interest - $effectivePayment;

            // Jika saldo sudah lunas, set ke 0 dan hentikan loop
            if ($currentBalance <= 0) {
                $predictions[] = [
                    'date' => $currentDate->addMonth()->format('Y-m-d'),
                    'balance' => 0,
                    'monthly_interest' => round($interest, 2),
                ];
                break;
            }

            // Tambahkan ke array prediksi
            $predictions[] = [
                'date' => $currentDate->addMonth()->format('Y-m-d'),
                'balance' => round($currentBalance, 2),
                'monthly_interest' => round($interest, 2),
            ];
        }

        return $predictions;
    }

    private function getCashAccounts($budgetId)
    {
        return Account::where('budget_id', $budgetId)
            ->where('type', 'cash')
            ->get();
    }

    private function getCurrentAllCashAccountsBalance($budgetId)
    {
        $cashAccounts = $this->getCashAccounts($budgetId);
        $totalBalance = 0;

        foreach ($cashAccounts as $account) {
            $totalBalance += $account->balance;
        }

        return $totalBalance;
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
                    'balance' => (float) $account->balance,
                    'interest' => (float) $account->interest,
                    'minimum_payment_monthly' => (float) $account->minimum_payment_monthly,
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
