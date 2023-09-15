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
        return $this->hasMany(Empresa::class, 'Empresa', 'Empresa');
    }

}
