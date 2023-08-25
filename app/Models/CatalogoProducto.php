<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoProducto extends Model
{
    use HasFactory;

    protected $table = 'CATALOGOPRODUCTO';
    protected $primaryKey = 'CatalogoProducto';
    public $timestamps = false;

    protected $fillable = [
        'Catalogo',
        'Producto',
        'UnidadMedida',
        'UnidadBulto',
        'Moneda',
        'PrecioVenta',
        'PrecioIva',
        'Iva',
        'ImpuestoInterno',
        'PrecioFinal',
        'Estado',
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'Catalogo', 'Catalogo');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto', 'Producto');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'UnidadMedida', 'UnidadMedida');
    }
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'Moneda', 'Moneda');
    }
}
