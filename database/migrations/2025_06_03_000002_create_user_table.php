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
        Schema::create('user', function (Blueprint $table) {
            $table->string('userId', 20)->primary();
            $table->string('username', 20)->unique();
            $table->string('email', 50)->unique();
            $table->string('passwordHash', 255);
            $table->string('name', 255);
            $table->string('userRole', 20)->default('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
?>
