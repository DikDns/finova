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
        Schema::create('account', function (Blueprint $table) {
            $table->string('accountId', 20)->primary();
            $table->string('budgetId', 20);
            $table->string('accountName', 255);
            $table->decimal('balance', 10, 2);
            $table->timestamps();
            
            $table->foreign('budgetId')->references('budgetId')->on('budget')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account');
    }
};
?>