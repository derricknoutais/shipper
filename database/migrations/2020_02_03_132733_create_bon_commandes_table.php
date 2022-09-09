<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonCommandesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bon_commandes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('nom');
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('demande_id');
            $table->unsignedBigInteger('fournisseur_id')->nullable();
            $table->unsignedBigInteger('facture_id')->nullable();
            $table->unsignedBigInteger('bon_commande_id')->nullable();
            $table->unsignedBigInteger('bon_livraison_id')->nullable();
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('bon_commandes');
    }
}
