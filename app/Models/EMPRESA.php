<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMPRESA extends Model
{
    use HasFactory;

    protected $table = 'EMPRESA';
    protected $primaryKey = 'Empresa';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'RazonSocial',
        'Estado',
        'Fecha',
        'Nit',
        'Telefono',
        'Direccion',
        'TipoEmpresa',
        'TipoSoftware',
        'Contacto',
        'Email',
        'Web',
        'Latitud',
        'Longitud',
        'Ciudad',
        'WhatsApp',
        'FaceBook',
        'Twitter',
        'Youtube',
        'Eslogan',
        'UrlLogo',
        'UrlPortada',
        'Email_TO',
        'Email_CC',
        'Email_CCO',
        'DecimalesCantidad',
        'Cliente',
        'ServerIP',
        'CadenaConexion',
        'ComerceID',
        'Usr',
        'UsrHora',
        'UsrFecha',
        'UnidadMedida',
        'RemitenteNombre',
        'RemitenteEmail',
        'Pais',
        'Region',
        'IpServidor',
        'IpProxy',
    ];


    public function pais()
    {
        return $this->belongsTo(Pais::class, 'Pais', 'Pais');
    }


    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'Empresa', 'Empresa');
    }
}


/**
 *
 * protected $table = 'EMPRESA';
    protected $primaryKey = 'Empresa';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'RazonSocial',
        'Estado',
        'Fecha',
        'Nit',
    ];

 */

