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
        Schema::create('user_log', function (Blueprint $table) {
            $table->string('logId', 20)->primary();
            $table->string('userId', 20);
            $table->string('username', 50);
            $table->string('email', 100);
            $table->string('passwordHash', 255);
            $table->string('name', 255);
            $table->string('userRole', 20);
            $table->date('createdAt');
            $table->timestamps();
            
            $table->foreign('userId')->references('userId')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_log');
    }
};
?>