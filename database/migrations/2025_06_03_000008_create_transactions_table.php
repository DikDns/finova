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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id');
            $table->foreignUuid('category_id');
            $table->foreignUuid('budget_id')->constrained()->onDelete('cascade');
            $table->string('payee', 255);
            $table->dateTime('date');
            $table->decimal('amount', 19, 4);
            $table->text('memo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
