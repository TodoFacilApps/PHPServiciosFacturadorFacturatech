<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    use HasFactory;

    protected $table = 'TIPOCLIENTE';
    protected $primaryKey = 'TipoCliente';
    public $timestamps = false;

    protected $fillable = [
        'TipoCliente',
        'Empresa',
        'Estado',
        'Descripcion',
        'PrecioPorMayor',
        'PrecioOferta',
        'PrecioRemate',
    ];



}
