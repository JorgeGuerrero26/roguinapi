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
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cantidad_entregada');
            $table->unsignedInteger('cantidad_recibida');
            $table->unsignedFloat('precio_unitario');
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('material_id');
            $table->timestamps();
            $table->foreign('venta_id')->references('id')->on('ventas');
            $table->foreign('material_id')->references('id')->on('materiales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
