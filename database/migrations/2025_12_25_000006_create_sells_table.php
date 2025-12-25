<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('seller_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('product_id');
            $table->index('seller_user_id');
            $table->index('buyer_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sells');
    }
};
