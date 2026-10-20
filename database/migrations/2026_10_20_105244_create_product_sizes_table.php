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
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size_name', 50); // Tên cỡ: Nhỏ, Vừa, Lớn
            $table->decimal('price', 12, 2); // Mức giá tương ứng của size
            $table->boolean('is_default')->default(false); // 1: Tùy chọn được chọn sẵn khi xem (Cỡ Vừa)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
