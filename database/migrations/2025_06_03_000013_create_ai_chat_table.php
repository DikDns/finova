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
        Schema::create('ai_chat', function (Blueprint $table) {
            $table->string('aiChatId', 20)->primary();
            $table->string('userId', 20);
            $table->string('role', 20);
            $table->string('content', 255);
            $table->json('categoryIds');
            $table->json('transactionIds');
            $table->json('accountIds');
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
        Schema::dropIfExists('ai_chat');
    }
};
?>