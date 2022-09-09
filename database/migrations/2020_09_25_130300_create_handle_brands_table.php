<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHandleBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('handle_brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('handle_id');
            $table->unsignedBigInteger('brand_id');

            $table->foreign('handle_id')->references('id')->on('handles');
            $table->foreign('brand_id')->references('id')->on('handles');
            $table->unique(['handle_id', 'brand_id']);
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
        Schema::dropIfExists('handle_brands');
    }
}
