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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // user_id nullable cho phép khách vãng lai mua hàng không cần đăng nhập
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_code', 50)->unique(); // Mã đơn: #LH-8291
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null');
            $table->string('payment_method', 30); // cod, vnpay, momo, bank_transfer
            $table->string('payment_status', 30)->default('pending');
            $table->string('order_status', 30)->default('received');
            $table->string('transaction_ref', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};