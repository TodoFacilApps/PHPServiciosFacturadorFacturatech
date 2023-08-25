<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;
    protected $table = 'VENTADETALLE';
    protected $primaryKey = 'VentaDetalle';
    public $timestamps = false;

    protected $fillable = [
        'Venta',
        'Cantidad',
        'Producto',
        'UnidadMedida',
        'PrecioVenta',
        'Descuento',
        'MontoVenta',
        'MontoDescuento',
        'SubTotal',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'Venta', 'Venta');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto', 'Producto');
    }
    public function unidadMedida()
    {
        return $this->belongsTo(unidadMedida::class, 'unidadMedida', 'unidadMedida');
    }
}
