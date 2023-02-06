<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->string('numero_guia')->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('usuario_id');            
            $table->unsignedBigInteger('entrega_id');
            $table->timestamps();
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('entrega_id')->references('id')->on('entregas');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};
