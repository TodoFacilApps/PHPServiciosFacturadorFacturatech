<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentoSector extends Model
{
    use HasFactory;

    protected $table = 'TIPODOCUMENTOSECTOR';

    protected $primaryKey = 'TIPODOCUMENTOSECTOR';

    protected $fillable = [
        'NOMBRE',
        'ServicioWeb',
        'TipoDocumentoFiscal',
        'TipoFacturaDocumento',
        'Caracteristicas',
        'TablaAConsultar',
        'DetalleAConsultar',
        'FormatoReporte',
        'URLIMAGEN',
        'POSICION',
        'TituloDocumento',
        'SubTituloDocumento',
        'DigitosFactura',
        'DecimalesFactura',
        'DigitosFacturaDetalle',
        'DecimalesFacturaDetalle',
        'Decimales',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];
}
