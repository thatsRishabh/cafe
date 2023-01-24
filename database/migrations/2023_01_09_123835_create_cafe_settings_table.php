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
        Schema::create('cafe_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cafe_id')->nullable();
            $table->foreign('cafe_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('cafe_id')->on('cafe_settings')->onDelete('cascade');
            // $table->string('email');
            $table->string('name')->nullable();
            $table->string('website')->nullable();
            $table->integer('subscription_charge')->nullable();
            $table->date('subscription_startDate')->nullable();
            $table->date('subscription_endDate')->nullable();
            $table->boolean('subscription_type')->comment('1 for monthly, 2 for annual')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
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
        Schema::dropIfExists('cafe_settings');
    }
};
