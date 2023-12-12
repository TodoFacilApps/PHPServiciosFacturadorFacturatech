<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'INVENTARIO';
    protected $primaryKey = 'Inventario';
    public $timestamps = false;

    protected $fillable = [
        'Sucursal',
        'Producto',
        'CantidadDisponible'
    ];
}
