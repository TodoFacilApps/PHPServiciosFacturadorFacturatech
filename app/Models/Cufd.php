<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cufd extends Model
{
    use HasFactory;
    protected $table = 'CUFD';
    protected $primaryKey = 'Cufd';
    public $timestamps = false;

    protected $fillable = [
        'Empresa',
        'Asociacion',
        'CodigoCUFD',
        'CufdAnterior',
        'FechaSolicitud',
        'HoraSolicitud',
        'FechaVigencia',
        'HoraVigencia',
        'Cerrado',
        'FechaCerrado',
        'HoraCerrado',
        'Certificacion',
        'CodigoSistema',
        'CodigoCUIS',
        'CodigoAmbiente',
        'CodigoModalidad',
        'Sucursal',
        'CodigoSucursal',
        'PuntoVenta',
        'CodigoPuntoVenta',
        'NumeroFactura',
        'CodigoControl',
        'EmpresaParametros',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];

}
