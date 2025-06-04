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
        Schema::create('export_report', function (Blueprint $table) {
            $table->string('reportId', 20)->primary();
            $table->string('userId', 20);
            $table->string('budgetId', 20);
            $table->date('generatedDate');
            $table->string('reportLink', 255);
            $table->timestamps();
            
            $table->foreign('userId')->references('userId')->on('user')->onDelete('cascade');
            $table->foreign('budgetId')->references('budgetId')->on('budget')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_report');
    }
};
?>