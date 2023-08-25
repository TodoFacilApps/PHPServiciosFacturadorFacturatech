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
        'Codigo',
        'Nombre',
        'Sucursal',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'Sucursal', 'Sucursal');
    }
}
