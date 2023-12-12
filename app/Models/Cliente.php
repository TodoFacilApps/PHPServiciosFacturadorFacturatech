<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'CLIENTE';
    protected $primaryKey = 'Cliente';
    public $timestamps = false;

    protected $fillable = [
        'Empresa',
        'TipoCliente',
        'CodigoCliente',
        'RazonSocial',
        'TipoDocumento',
        'Documento',
        'Complemento',
        'NitEspecial',
        'Telefono',
        'Email',
        'Usr',
        'UsrFecha',
        'UsrHora',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'Empresa', 'Empresa');
    }
    public function tipoCliente()
    {
        return $this->belongsTo(TipoCliente::class, 'TipoCliente', 'TipoCliente');
    }
}



/**
 * pruevas de nit
 *  12413709016 => Nit Activo => transacicon true =>codigo 986
 * independiente 3570744017 => Nit Activo => transacicon true =>codigo  987
 *
 * independiente 7722992010 => Nit Inactivo => transaccion true =>codigo 987
 * dependiente 9788381015 NO LOS RECONOSE
 * dependiente 9788381017 NO LOS RECONOSE
 *
 * pruevas con nit no registrados
 *
 * nro 4568834 => "NIT INEXISTENTE"=> transaccion false => codigo 994
 *
 */
