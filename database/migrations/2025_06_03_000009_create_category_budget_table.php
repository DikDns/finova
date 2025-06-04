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
        Schema::create('category_budget', function (Blueprint $table) {
            $table->string('categoryBudgetId', 20)->primary();
            $table->string('monthBudgetId', 20);
            $table->string('categoryId', 20);
            $table->date('categoryMonth');
            $table->decimal('catBudgetAssigned', 10, 2);
            $table->decimal('catBudgetActivity', 10, 2);
            $table->decimal('catBudgetAvailable', 10, 2);
            $table->timestamps();
            
            $table->foreign('monthBudgetId')->references('monthBudgetId')->on('monthly_budget')->onDelete('cascade');
            $table->foreign('categoryId')->references('categoryId')->on('category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_budget');
    }
};
?>