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
        Schema::create('category_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('monthly_budget_id');
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->decimal('assigned', 19, 4);
            $table->decimal('activity', 19, 4);
            $table->decimal('available', 19, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_budgets');
    }
};
