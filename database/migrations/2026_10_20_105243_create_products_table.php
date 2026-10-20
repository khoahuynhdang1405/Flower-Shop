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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->decimal('base_price', 12, 2); // Giá tiêu chuẩn cỡ vừa
            $table->text('description')->nullable();
            $table->string('dimension_info', 255)->nullable(); // Kích thước bó hoa
            $table->string('freshness_guarantee', 255)->nullable(); // Cam kết độ tươi
            $table->unsignedInteger('stock')->default(0); // Số lượng có thể cắm hôm nay
            $table->boolean('is_featured')->default(false); // 1: Hiển thị mục nổi bật
            $table->boolean('is_active')->default(true); // 1: Đang bán, 0: Ngừng bán
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