<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimineto extends Model
{
    use HasFactory;

    protected $table = 'MOVIMIENTO';
    protected $primaryKey = 'Movimiento';
    public $timestamps = false; // Esto desactiva la gestión de marcas de tiempo created_at y updated_at

    protected $fillable = [
        'Empresa',
        'Sucursal',
        'PuntoVenta',
        'Producto',
        'Cantidad',
        'TipoMovimiento',
        'Motivo',
        'Fecha',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];
}
