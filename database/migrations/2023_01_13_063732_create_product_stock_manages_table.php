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
        Schema::create('product_stock_manages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->comment('This will be from product_infos(id) table')->nullable();
            $table->foreign('product_id')->references('id')->on('product_infos')->onDelete('cascade'); 
            $table->integer('old_stock')->nullable();
            $table->float('change_stock')->nullable();
            $table->integer('new_stock')->nullable();
            $table->enum('stock_operation', ['Out', 'In']);
            $table->integer('unit_id')->nullable();
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
        Schema::dropIfExists('product_stock_manages');
    }
};
