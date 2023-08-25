<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaSucursal extends Model
{
    use HasFactory;

    protected $table = 'EMPRESASUCURSAL';
    protected $primaryKey = ['Empresa', 'Sucursal'];
    public $incrementing = false;

    protected $fillable = [
        'Empresa',
        'Sucursal',
        'CodigoSucursal',
        'Estado',
        'TipoEmisionModo',
        'Telefono',
        'Direccion',
        'Localidad',
        'CantidadDePuntoVenta',
        'ServerIPRadminVPN',
        'SincronizarCUFDASucursal',
        'BaseDeDatos',
        'UserDataBase',
        'PassDataBase',
        'ServerDBLocal',
        'DBLocal',
        'UsrDBLocal',
        'PassDBLocal',
        'TipoDBLocal',
    ];

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'Empresa', 'Empresa');
    }
}
