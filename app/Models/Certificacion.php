<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    use HasFactory;
    protected $table = 'CERTIFICACION';

    protected $primaryKey = 'Certificacion';

    protected $fillable = [
        'Empresa',
        'RazonSocial',
        'Nit',
        'NombreSistema',
        'VersionSistema',
        'TipoSistema',
        'CodigoAmbiente',
        'CodigoModalidad',
        'CodigoSistema',
        'Estado',
        'Etapa',
        'FechaSolicitud',
        'HoraSolcitud',
        'FechaCertificacion',
        'SolicitudSIAT',
        'FechaCorteSIAT',
        'HoraCorteSIAT',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];
}
