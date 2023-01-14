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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('product_menu_id')->nullable();
            $table->foreign('product_menu_id')->references('id')->on('product_menus')->onDelete('cascade');
            // $table->integer('product_menu_id', 20)->nullable();
            $table->string('name', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('recipe_status')->comment('1 means inactive, 2 means active')->nullable();
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
        Schema::dropIfExists('recipes');
    }
};
