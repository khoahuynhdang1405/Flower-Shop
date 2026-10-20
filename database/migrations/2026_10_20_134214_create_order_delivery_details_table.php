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
        Schema::create('order_delivery_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
            $table->string('sender_name', 255);
            $table->string('sender_phone', 20);
            $table->string('sender_email', 255);
            $table->boolean('is_gift')->default(true); // 1: Gửi tặng người thương, 0: Tự nhận
            $table->string('receiver_name', 255);
            $table->string('receiver_phone', 20);
            $table->string('shipping_address', 500);
            $table->string('shipping_district', 100);
            $table->date('delivery_date');
            $table->string('time_slot', 50); // VD: 14:00-16:00
            $table->string('card_sender', 100)->nullable();
            $table->string('card_receiver', 100)->nullable();
            $table->text('card_message')->nullable();
            $table->text('delivery_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_details');
    }
};
