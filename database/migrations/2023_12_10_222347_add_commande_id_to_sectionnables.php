<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sectionnables', function (Blueprint $table) {
            $table->bigInteger('commande_id')->nullable();
            $table
                ->foreign('commande_id')
                ->references('id')
                ->on('commandes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectionnables', function (Blueprint $table) {
            //
        });
    }
};