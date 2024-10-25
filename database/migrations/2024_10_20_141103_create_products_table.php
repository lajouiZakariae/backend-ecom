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
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            $table->string('name');

            $table->text('description')->nullable();

            $table->decimal('price', 10, 2)->nullable();

            $table->timestamp('published_at')->nullable();

            $table->unsignedBigInteger('category_id')->nullable()->default(1);

            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('store_id')->nullable()->default(1);

            $table->foreign('store_id')->references('id')->on('stores');

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
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
