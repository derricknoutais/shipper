<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandeSectionnableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demande_sectionnable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sectionnable_id');
            $table->unsignedBigInteger('demande_id');
            $table->string('traduction')->nullable();
            $table->unsignedBigInteger('quantite_offerte')->default(0);
            $table->unsignedBigInteger('offre')->default(0);

            $table->boolean('differente_offre')->nullable(0);
            $table->string('reference_differente_offre');
            $table->boolean('checked')->default(0);
            $table->unique(['sectionnable_id', 'demande_id']);
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
        Schema::dropIfExists('demande_sectionnable');
    }
}
