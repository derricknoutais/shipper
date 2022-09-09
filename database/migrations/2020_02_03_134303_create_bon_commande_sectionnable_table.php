<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonCommandeSectionnableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bon_commande_sectionnable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sectionnable_id');
            $table->unsignedBigInteger('bon_commande_id');
            $table->unsignedBigInteger('quantite');
            $table->unsignedBigInteger('prix_achat');
            $table->string('traduction')->nullable();
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
        Schema::dropIfExists('bon_commande_sectionnable');
    }
}
