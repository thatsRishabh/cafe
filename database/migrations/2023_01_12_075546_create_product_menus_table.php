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
        Schema::create('product_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->comment('This will be from category(id) table')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // $table->unsignedBigInteger('subcategory_id')->comment('This will be from subcategory(id) table')->nullable();
            // $table->foreign('subcategory_id')->references('id')->on('categories')->onDelete('cascade');
            // $table->unsignedBigInteger('parent_id')->nullable();
            // $table->foreign('parent_id')->references('id')->on('product_menus')->onDelete('cascade');
            // $table->boolean('is_parent')->comment('1 means Yes, 2 no')->nullable();

            $table->string('name', 50)->nullable();
            $table->text('description')->nullable();
            // $table->integer('parent_id')->nullable();
            // $table->boolean('is_parent')->nullable();
            $table->string('image')->nullable();
            $table->integer('order_duration')->nullable();
            $table->integer('priority_rank')->nullable();
            // $table->text('image_url')->nullable();
            $table->integer('price') ->comment('This will be used to subtract from Expense')->nullable();
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
        Schema::dropIfExists('product_menus');
    }
};
