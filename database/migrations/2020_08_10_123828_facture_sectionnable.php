<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FactureSectionnable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facture_sectionnable', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sectionnable_id');
            $table->unsignedBigInteger('facture_id');
            $table->unsignedBigInteger('quantite');
            $table->unsignedBigInteger('prix_achat');

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
        Schema::dropIfExists('facture_sectionnable');
    }
}
