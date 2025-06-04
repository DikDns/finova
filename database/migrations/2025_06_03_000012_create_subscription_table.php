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
        Schema::create('subscription', function (Blueprint $table) {
            $table->string('subscriptionId', 20)->primary();
            $table->string('userId', 20);
            $table->string('invoice', 100);
            $table->string('paymentMethod', 100);
            $table->date('startDate');
            $table->date('endDate');
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
        Schema::dropIfExists('subscription');
    }
};
?>