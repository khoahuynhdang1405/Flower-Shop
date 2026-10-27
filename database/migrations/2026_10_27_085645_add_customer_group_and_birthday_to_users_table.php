<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nhóm khách và ngày sinh
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('customer_group', 20)->default('new')->after('google_id');
            $table->date('birthday')->nullable()->after('customer_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['customer_group', 'birthday']);
        });
    }
};
