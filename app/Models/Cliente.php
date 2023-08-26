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
        'Nombre',
        'Apellidos',
        'TipoDocumento',
        'Documento',
        'Direccion',
        'Email',
        'Telefono',
        'Usr',
        'UsrFecha',
        'UsrHora'
    ];

    protected $casts = [
        'TipoDocumento' => 'integer',
        'Usr' => 'integer',
    ];

    protected $dates = [
        'UsrFecha',
    ];

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'Empresa', 'Empresa');
    }

}
