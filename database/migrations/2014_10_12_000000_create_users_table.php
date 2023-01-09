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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->foreign('store_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            // $table->string('email')->unique();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->integer('role_id')->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->text('address')->nullable();
            $table->date('joining_date')->comment('This will be in yyyy-mm-dd')->nullable();
            $table->date('birth_date')->comment('This will be in yyyy-mm-dd')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->integer('salary')->nullable();
            $table->integer('salary_balance')->comment('Its initial value will be equal to salar')->nullable();
            $table->string('image')->nullable();
            $table->integer('account_balance')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
