<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_budget', function (Blueprint $table) {
            $table->string('monthBudgetId', 20)->primary();
            $table->string('budgetId', 20);
            $table->date('budgetMonth');
            $table->decimal('monthBudgetIncome', 10, 2);
            $table->decimal('monthBudgetAssigned', 10, 2);
            $table->decimal('monhtBudgetActivity', 10, 2);
            $table->decimal('monthBudgetAvailable', 10, 2);
            $table->timestamps();
            
            $table->foreign('budgetId')->references('budgetId')->on('budget')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_budget');
    }
};

?>