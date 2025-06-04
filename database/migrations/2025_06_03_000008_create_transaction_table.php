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
        Schema::create('transaction', function (Blueprint $table) {
            $table->string('transactionId', 20)->primary();
            $table->string('accountId', 20);
            $table->string('categoryId', 20);
            $table->string('budgetId', 20);
            $table->string('payee', 255);
            $table->date('transactionDate');
            $table->decimal('transactionAmount', 10, 2);
            $table->string('transactionMemo', 255);
            $table->timestamps();
            
            $table->foreign('accountId')->references('accountId')->on('account')->onDelete('cascade');
            $table->foreign('categoryId')->references('categoryId')->on('category')->onDelete('cascade');
            $table->foreign('budgetId')->references('budgetId')->on('budget')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};

?>