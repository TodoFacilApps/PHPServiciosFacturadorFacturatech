<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asociacion extends Model
{
    use HasFactory;
    protected $table = 'ASOCIACION';
    protected $primaryKey = 'Asociacion';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Empresa',
        'NitEmpresa',
        'Estado',
        'ModoCertificacion',
        'FechaAsociacion',
        'FechaVigencia',
        'Certificacion',
        'CodigoSistema',
        'CodigoAmbiente',
        'TipoModalidad',
        'TipoServicio',
        'SolicitarCUFDPorDemon',
        'HoraSolicitudCUFDPorDemon',
        'AsociacionCredencial',
        'Login',
        'Correos',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];

}
