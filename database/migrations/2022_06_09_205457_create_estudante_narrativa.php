<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstudanteNarrativa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estudante_narrativa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained();
            $table->foreignId('narrativa_id')->constrained();
            $table->boolean('is_author')->default(false);
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
        Schema::dropIfExists('estudante_narrativa');
    }
}
