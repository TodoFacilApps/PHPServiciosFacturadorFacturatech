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
        'Fecha',
        'Hora',
        'SubTotal',
        'TotalDesc',
        'TotalVenta',
        'Cargo',
        'GiftCard',
        'TotalPagar',
        'ImporteIva',
        'Moneda',
        'MetodoPago',
        'Nro4Init',
        'Nro4Fin',
        'Cliente',
        'TipoPago',
        'CodigoPago',
        'CodigoCredito',
    ];


    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'Empresa');
    }
    public function sucursal()
    {
        return $this->belongsTo(EmpresaSucursal::class, 'Sucurasal');
    }
    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'PuntoVenta');
    }



    public function moneda()
    {
        return $this->hasMany(Moneda::class, 'Moneda', 'Moneda');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class,'Venta');
    }
}
/**
 * Agregar un descuento general en la tabla Venta
 */


 /**
  * posible asocioacion de otra tabla o creacion de muntiples campos para la facturacion
  */
