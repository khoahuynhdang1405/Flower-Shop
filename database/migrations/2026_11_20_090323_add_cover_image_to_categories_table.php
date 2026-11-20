<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ảnh chính (ảnh bìa) của bộ sưu tập: đường dẫn trên disk public, vd. collections/abc.webp
     * (docs/design-vs-db-gaps.md #2).
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('cover_image', 255)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('cover_image');
        });
    }
};
