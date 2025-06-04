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
        Schema::create('budget', function (Blueprint $table) {
            $table->string('budgetId', 20)->primary();
            $table->string('userId', 20);
            $table->string('budgetName', 255);
            $table->string('currencyType', 100);
            $table->date('createdAt');
            $table->date('updatedAt');
            $table->timestamps();
            
            $table->foreign('userId')->references('userId')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget');
    }
};
?>
