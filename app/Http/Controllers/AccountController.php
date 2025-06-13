<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Account;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index(Budget $budget)
    {
        $accounts = Account::where('budget_id', $budget->id)->get();
        return Inertia::render('app/Accounts', [
            'budget' => $budget,
            'accounts' => $accounts
        ]);
    }
}
