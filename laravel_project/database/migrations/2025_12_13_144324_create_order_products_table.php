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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id('order_product_id');

            $table->foreignId('order_id')->constrained('orders', 'order_id')->onDelete('cascade');

            $table->foreignId('product_id')->nullable()->constrained('products', 'product_id')->onDelete('set null'); 
            
            $table->integer('quantity');
            $table->float('price_when_purchased', 8, 2);

            $table->unique(['order_id', 'product_id']);
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
    
};