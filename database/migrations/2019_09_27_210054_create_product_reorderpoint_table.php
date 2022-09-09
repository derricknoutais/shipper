<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReorderpointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reorderpoint', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->unsignedBigInteger('reorderpoint_id');
            $table->string('product_id');

            $table->foreign('reorderpoint_id')->references('id')->on('reorderpoints')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('product_reorderpoint');
    }
}
