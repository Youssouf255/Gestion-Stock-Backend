<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique();
            $table->decimal('order_value', 10, 2);
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('buying_price', 10, 2);
            $table->date('expected_delivery');
            $table->enum('status', ['confirmed', 'delayed', 'out_for_delivery', 'returned'])->default('confirmed');
            $table->boolean('notify_on_delivery')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}











