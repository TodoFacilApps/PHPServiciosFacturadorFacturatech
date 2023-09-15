<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaToken extends Model
{
    use HasFactory;

    protected $table = 'EMPRESATOKEN';
    protected $primaryKey = ['Empresa', 'Serial'];
    public $incrementing = false;
    protected $fillable = [
        'Empresa',
        'Serial',
        'Estado',
        'TipoToken',
        'FechaCreacion',
        'FechaLimite',
        'MetodoCreacionToken',
        'Titulo',
        'TokenService',
        'TokenSecret',
        'UrlCallBack',
        'UrlReturn',
        'UrlFactura',
        'MensajeCliente',
    ];
}
