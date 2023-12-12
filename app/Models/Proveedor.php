<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'PROVEEDOR';
    protected $primaryKey = 'Proveedor';
    public $timestamps = false;

    protected $fillable = [
        'TipoDocumento',
        'Documenton',
        'Nombre',
        'CodigoInterno',
        'Correo',
        'DomicilioFiscal',
        'ContactoFiscal',
        'NombrePersonalAcargo',
        'ContactoPersonalAcargo',
        'Empresa',
    ];
}
