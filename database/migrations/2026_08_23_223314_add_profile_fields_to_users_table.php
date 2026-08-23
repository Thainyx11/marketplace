<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('acheteur')->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->text('shipping_address')->nullable()->after('bio');
            $table->string('shop_name')->nullable()->after('shipping_address');
            $table->string('shop_slug')->nullable()->unique()->after('shop_name');
            $table->text('payout_note')->nullable()->after('shop_slug');
            $table->boolean('is_approved')->default(true)->after('payout_note');
            $table->boolean('is_active')->default(true)->after('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'avatar', 'bio', 'shipping_address',
                'shop_name', 'shop_slug', 'payout_note', 'is_approved', 'is_active',
            ]);
        });
    }
};
