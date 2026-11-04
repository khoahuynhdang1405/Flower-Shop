<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sắc hoa của sản phẩm (nhiều sắc / sản phẩm); giá trị theo App\Enums\FlowerColor (docs/design-vs-db-gaps.md #3).
     */
    public function up(): void
    {
        Schema::create('product_colors', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('color', 20);

            $table->primary(['product_id', 'color']);
            $table->index('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
