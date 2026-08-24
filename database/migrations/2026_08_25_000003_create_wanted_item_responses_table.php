<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanted_item_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wanted_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['wanted_item_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanted_item_responses');
    }
};
