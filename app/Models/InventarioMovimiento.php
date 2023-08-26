<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    use HasFactory;
    protected $table = 'INVENTARIOMOVIMIENTO';
    protected $primaryKey = 'InventarioMovimiento';
    public $timestamps = false;

    protected $fillable = [
        'Sucursal',
        'PuntoVenta',
        'TipoMovimiento',
        'Cantidad',
        'FechaHora',
        'Nota'
    ];

    protected $dates = [
        'FechaHora'
    ];
}
