<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'VENTA';
    protected $primaryKey = 'Venta';
    public $timestamps = false;

    protected $fillable = [
        'Empresa',
        'Sucursal',
        'PuntoVenta',
        'Cliente',
        'Fecha',
        'Total',
        'Moneda',
    ];

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'Empresa', 'Empresa');
    }

    public function sucursal()
    {
        return $this->hasMany(Sucursal::class, 'Sucursal', 'Sucursal');
    }

    public function puntoVenta()
    {
        return $this->hasMany(PuntoVenta::class, 'PuntoVenta', 'PuntoVenta');
    }

    public function moneda()
    {
        return $this->hasMany(Moneda::class, 'Moneda', 'Moneda');
    }

}
