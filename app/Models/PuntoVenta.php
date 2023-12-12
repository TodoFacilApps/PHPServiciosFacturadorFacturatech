<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoVenta extends Model
{
    use HasFactory;
    protected $table = 'PUNTOVENTA';
    protected $primaryKey = 'PuntoVenta';
    public $timestamps = false;

    protected $fillable = [
        'CodigoPuntoVenta',
        'Nombre',
        'Estado',
        'Descripcion',
        'CodigoAmbiente',
        'CodigoModalidad',
        'Sucursal',
        'CodigoSucursal',
        'CodigoTipoPuntoVenta',
        'CodigoSistema',
        'Cuis',
        'Nit',
        'SolicitarCUFDPorDemon',
        'HoraSolicitudCUFDPorDemon',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'Sucursal', 'Sucursal');
    }
}
