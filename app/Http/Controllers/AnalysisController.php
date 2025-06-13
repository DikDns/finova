<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnalysisController extends Controller
{
    public function index(Budget $budget)
    {
        return Inertia::render('app/Analysis', [
            'budget' => $budget
        ]);
    }
}
