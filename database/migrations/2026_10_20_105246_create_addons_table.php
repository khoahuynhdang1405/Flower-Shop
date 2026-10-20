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
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->decimal('price', 12, 2)->default(0); // Giá quà kèm
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_free')->default(false); // 1: Quà miễn phí (như thiệp viết tay)
            $table->boolean('is_active')->default(true); // 1: Cho phép khách chọn
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
