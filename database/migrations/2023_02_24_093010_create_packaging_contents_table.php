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
        Schema::create('packaging_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('packaging_id')->nullable();
            $table->foreign('packaging_id')->references('id')->on('packagings')->onDelete('cascade');
            $table->unsignedBigInteger('product_info_stock_id')->nullable();
            $table->foreign('product_info_stock_id')->references('id')->on('product_infos')->onDelete('cascade');
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('packaging_contents');
    }
};
