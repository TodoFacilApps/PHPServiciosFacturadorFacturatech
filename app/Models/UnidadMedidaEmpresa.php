<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedidaEmpresa extends Model
{
    use HasFactory;

    protected $table = 'UNIDADMEDIDAEMPRESA';
    protected $primaryKey = 'UnidadMedidaEmpresa';
    public $timestamps = false;
    protected $fillable = [
        'Empresa',
        'Codigo',
        'Estado',
        'Usr',
        'UsrFecha',
        'UsrHora'
    ];

}
