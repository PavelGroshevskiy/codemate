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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deposit', 'withdraw', 'transfer_in','transfer_out'])->default('deposit');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->foreignId('from_user_id')->constrained( 'users');
            $table->foreignId('to_user_id')->nullable()->constrained( 'users');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['from_user_id', 'to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
