<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('reference_type'); // opening_balance, order, payment, return, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0); // Customer owes us
            $table->decimal('credit', 15, 2)->default(0); // Customer paid us
            $table->decimal('running_balance', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
