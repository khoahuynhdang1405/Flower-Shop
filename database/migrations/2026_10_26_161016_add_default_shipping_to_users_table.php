<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Địa chỉ giao hoa mặc định của khách (docs/design-vs-db-gaps.md #9).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_shipping_address', 500)->nullable()->after('google_id');
            $table->string('default_shipping_district', 100)->nullable()->after('default_shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['default_shipping_address', 'default_shipping_district']);
        });
    }
};
