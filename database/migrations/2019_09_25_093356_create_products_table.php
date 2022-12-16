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
            $table->string('source_id')->nullable();
            $table->string('source_variant_id')->nullable();
            $table->string('variant_parent_id')->nullable();
            $table->string('name')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('handle')->nullable();
            $table->string('sku')->nullable();
            $table->string('supplier_code')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('ecwid_enabled_webstore')->nullable();
            $table->boolean('has_inventory')->nullable();
            $table->boolean('is_composite')->nullable();
            $table->string('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
            $table->string('deleted_at')->nullable();
            $table->string('source')->nullable();
            $table->string('account_code')->nullable();
            $table->string('account_code_purchase')->nullable();
            $table->string('supply_price')->nullable();
            $table->string('version')->nullable();
            // $table->text('type')->nullable();
            // $table->text('product_category')->nullable();
            // $table->string('supplier')->nullable();
            // $table->text('brand')->nullable();
            // $table->text('variant_options')->nullable();
            // $table->text('categories')->nullable();
            // $table->text('images')->nullable();
            // $table->text('skuImages')->nullable();
            // $table->boolean('has_variants')->nullable();
            // $table->integer('variant_count')->nullable();
            // $table->integer('button_order')->nullable();
            // $table->integer('price_including_tax')->nullable();
            // $table->integer('price_excluding_tax')->nullable();
            // $table->integer('loyalty_amount')->nullable();
            // $table->text('product_codes')->nullable();
            // $table->text('product_suppliers')->nullable();
            // $table->text('packaging')->nullable();
            // $table->integer('weight')->nullable();
            // $table->string('weight_unit')->nullable();
            // $table->double('length')->nullable();
            // $table->double('width')->nullable();
            // $table->double('height')->nullable();
            // $table->string('dimensions_unit')->nullable();
            // $table->text('attributes')->nullable();
            // $table->boolean('is_active')->nullable();
            // $table->string('image_thumbnail_url')->nullable();
            // $table->string('product_type_id')->nullable();
            // $table->string('supplier_id')->nullable();
            // $table->text('brand_id')->nullable();
            // $table->text('tag_ids')->nullable();

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
