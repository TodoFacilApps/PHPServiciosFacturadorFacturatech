<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaFactura extends Model
{
    use HasFactory;

    protected $table = 'VENTAFACTURA';
    protected $primaryKey = 'Venta'; // Esto asume que 'Venta' es la clave primaria de la tabla
    public $timestamps = false;
    protected $fillable = [
        'Venta',
        'NumeroFactura',
        'NitEmison',
        'FechaEmision',
        'HoraEmision',
        'ValidoSin',
        'Moneda',
        'CodigoSucursal',
        'CodigoPuntoVenta',
        'TipoDocumentoSector',
        'CodigoCliente',
        'DocumentoIdentidad',
        'NumeroDocumento',
        'Complemento',
        'Codexcepci',
        'RazonSocial',
        'Email',
        'MetodoPago',
        'NumeroTarjeta',
        'Cufd',
        'Cuf',
        'Cafc',
        'Leyenda',
        'Nota',
        'GiftCard',
        'FechaCreacion',
        'HoraCreacion',
        'EstadoSiat',
        'Observacion',
        'TipoEmision',
        'Evento',
        'NitEspecial',
    ];
}
