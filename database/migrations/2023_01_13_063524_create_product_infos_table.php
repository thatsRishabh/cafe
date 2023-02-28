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
        Schema::create('product_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('unit_id')->comment('This will be from unit(id) table')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->string('name', 50);
            $table->text('description')->nullable();
            // $table->float('price', 12, 4);
            // $table->integer('minimum_qty')->nullable();
            $table->float('current_quanitity', 12, 4);
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
        Schema::dropIfExists('product_infos');
    }
};
