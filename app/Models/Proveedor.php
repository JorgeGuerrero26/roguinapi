<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    //Indicar que es de table proveedores
    protected $table = 'proveedores';
    //Indicar fillable
    protected $fillable = ['ruc', 'nombre'];
}
