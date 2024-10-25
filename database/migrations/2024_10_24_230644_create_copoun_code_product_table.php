<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('copoun_code_product', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('product_id');

            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('coupon_code_id');

            $table->foreign('coupon_code_id')->references('id')->on('coupon_codes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copoun_code_product');
    }
};
