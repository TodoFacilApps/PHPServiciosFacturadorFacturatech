<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaAuxiliar extends Model
{
    use HasFactory;
    protected $table = 'FACTURAAUXILIAR';
    protected $primaryKey = 'Factura';
    public $timestamps = false;
    public $incrementing = false;
    
    
    
    protected $fillable = [
        'Factura',
        'Cuf',
        'NumeroFactura',
        'ValidadoEnSin',
        'FechaEmision',
        'EstadoEnSiat',
        'NombreRazonSocial',
        'NitEmisor',
        'MontoTotal',
        'DescuentoAdicional',
        'CodigoSucursal',
        'CodigoPuntoVenta',
    ];

}
