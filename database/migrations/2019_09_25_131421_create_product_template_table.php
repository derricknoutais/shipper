<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_template', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('template_id');
            $table->string('product_id');
            $table->unsignedInteger('quantite');

            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('product_template');
    }
}
