<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        // handle account.balance and monthly_budget.total_balance sync
        $monthlyBudgets = $account->budget->monthlyBudgets();
        foreach ($monthlyBudgets as $monthlyBudget) {
            $monthlyBudget->total_balance = $monthlyBudget->total_balance + $account->balance;
            $monthlyBudget->save();
        }
    }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void
    {
        // handle account.balance and monthly_budget.total_balance sync
        $monthlyBudgets = $account->budget->monthlyBudgets();
        $balanceDifference = $account->balance - $account->getOriginal('balance');

        foreach ($monthlyBudgets as $monthlyBudget) {
            $monthlyBudget->total_balance += $balanceDifference;
            $monthlyBudget->save();
        }
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        //
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        //
    }
}
