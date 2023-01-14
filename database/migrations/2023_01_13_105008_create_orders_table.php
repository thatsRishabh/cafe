<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id')->comment('This will be from customer(id) table')->nullable();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->integer('table_number')->nullable();
            $table->boolean('order_status')->comment('1 means pending(by default), 2 means Approved')->nullable();
            $table->integer('mode_of_transaction')->comment('1 for cash, 2 for online')->nullable();
            $table->string('duration_expired', 50)->nullable();
            $table->integer('cartTotalQuantity')->nullable();
            $table->integer('cartTotalAmount')->nullable();
            $table->integer('taxes')->nullable();
            $table->integer('netAmount')->nullable();  
            $table->string('bill_pdf')->nullable();
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
};
