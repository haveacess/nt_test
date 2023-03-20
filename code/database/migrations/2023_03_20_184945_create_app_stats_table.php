<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_stats', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('id_country');
            $table->foreign('id_country')
                ->references('id')->on('countries')
                ->cascadeOnDelete();

            $table->unsignedInteger('id_app');
            $table->foreign('id_app')
                ->references('id')->on('apps')
                ->cascadeOnDelete();

            $table->date('date');
            $table->integer('top_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_stats');
    }
};
