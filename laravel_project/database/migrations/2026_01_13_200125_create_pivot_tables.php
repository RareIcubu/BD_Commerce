<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    // Tabela Koszyka
    Schema::create('carts', function (Blueprint $table) {
        $table->id('cart_id'); // Ważne: Twój model ma primaryKey = 'cart_id'
        $table->string('session_id')->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
    });

    // Tabela Zamówień
    Schema::create('orders', function (Blueprint $table) {
        $table->id('order_id'); // Ważne: Twój model ma primaryKey = 'order_id'
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('total_price', 10, 2);
        $table->string('status')->default('pending');
        $table->timestamps();
    });

    // PIVOT: Produkty w Koszyku (To musi pasować do Twojego modelu Cart)
    Schema::create('cart_product', function (Blueprint $table) {
        $table->id('cart_product_id');
        $table->foreignId('cart_id')->constrained('carts', 'cart_id')->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
        $table->integer('quantity')->default(1);
    });

    // PIVOT: Produkty w Zamówieniu (To musi pasować do Twojego modelu Order)
    Schema::create('order_product', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders', 'order_id')->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products', 'product_id');
        $table->integer('quantity');
        $table->decimal('price_when_purchased', 10, 2); // To musi pasować do withPivot w modelu Order
    });
}

public function down() {
    Schema::dropIfExists('order_product');
    Schema::dropIfExists('cart_product');
    Schema::dropIfExists('orders');
    Schema::dropIfExists('carts');
}};
