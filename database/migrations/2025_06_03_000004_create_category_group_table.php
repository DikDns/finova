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
        Schema::create('category_group', function (Blueprint $table) {
            $table->string('categoryGroupId', 20)->primary();
            $table->string('budgetId', 20);
            $table->string('categoryGroupName', 255);
            $table->timestamps();
            
            $table->foreign('budgetId')->references('budgetId')->on('budget')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_group');
    }
};
?>
