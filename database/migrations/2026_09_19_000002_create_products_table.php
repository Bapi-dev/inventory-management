<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('unit')->default('Pcs');
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('wholesale_price', 15, 2)->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->integer('current_stock')->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
