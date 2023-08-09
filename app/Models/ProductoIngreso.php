<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoIngreso extends Model
{
    use HasFactory;


    protected $table = 'PRODUCTOINGESO';

    protected $primaryKey = 'ProductoIngreso';
    public $timestamps = false;

    protected $fillable = [
        'Ingreso',
        'Producto',
        'UnidadMedida',
        'Cantidad',
        'Precio',
        'Total',
        'Estado',
    ];


    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto', 'Producto');
    }

    // Relación con Ingreso
    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'Ingreso', 'Ingreso');
    }

    // Relación con UnidadMedida
    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'UnidadMedida', 'UnidadMedida');
    }


}

