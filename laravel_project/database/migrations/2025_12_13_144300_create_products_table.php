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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');

            $table->foreignId('seller_id')->constrained('users', 'user_id');

            $table->foreignId('category_id')->constrained('categories', 'category_id');

            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('front_image')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->timestamp('modified_at')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
