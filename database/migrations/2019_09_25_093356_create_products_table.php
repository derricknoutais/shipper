<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('handle_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->double('price')->nullable();
            $table->double('quantity')->default(0);
            $table->double('reference')->nullable();
            $table->string('variant_option_one_name')->nullable();
            $table->string('variant_option_one_value')->nullable();
            $table->string('variant_option_two_name')->nullable();
            $table->string('variant_option_two_value')->nullable();
            $table->string('variant_option_three_name')->nullable();
            $table->string('variant_option_three_value')->nullable();
            $table->double('supply_price')->nullable();
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
        Schema::dropIfExists('products');
    }
}
