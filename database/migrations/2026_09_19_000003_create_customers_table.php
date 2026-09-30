<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('customer_type')->default('wholesaler'); // distributor, wholesaler, retailer, individual
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_type');
            $table->index('status');
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
