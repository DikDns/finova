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
        // Update ai_chats table
        Schema::table('ai_chats', function (Blueprint $table) {
            $table->uuid('budget_id')->nullable()->after('user_id');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('set null');
            $table->string('title')->nullable()->after('role');
            $table->longText('content')->nullable()->change();
        });

        // Create ai_messages table
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ai_chat_id')->constrained()->onDelete('cascade');
            $table->longText('content');
            $table->string('role', 20)->default('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');

        Schema::table('ai_chats', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
            $table->dropColumn('title');
            $table->longText('content')->nullable(false)->change();
        });
    }
};