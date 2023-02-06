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
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cantidad_comprada');
            $table->unsignedFloat('precio_unitario');
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('material_id');

            $table->timestamps();

            $table->foreign('compra_id')->references('id')->on('compras');
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
        Schema::dropIfExists('detalle_compras');
    }
};