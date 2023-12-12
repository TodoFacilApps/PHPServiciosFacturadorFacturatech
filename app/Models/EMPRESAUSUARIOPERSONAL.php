<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaUsuarioPersonal extends Model
{
    use HasFactory;

    protected $table = 'EMPRESAUSUARIOPERSONAL';
    protected $primaryKey = ['Empresa', 'Serial'];
    public $incrementing = false;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'Empresa',
        'Serial',
        'Usuario',
        'Sucursal',
        'PuntoVenta',
        'Estado',
        'CodigoAmbiente',
    ];

    // Relación con la tabla EMPRESA
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'Empresa', 'Empresa');
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Usuario', 'Usuario');
    }
    
    public function sucursal()
    {
        return $this->belongsTo(EmpresaSucursal::class, 'Sucursal', 'Sucursal');
    }

    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'PuntoVenta', 'PuntoVenta');
    }
    
}
