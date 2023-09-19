<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'UNIDADMEDIDA';
    protected $primaryKey = 'UnidadMedida';
    public $timestamps = false;
    protected $fillable = [
        'Empresa',
        'Codigo',
        'Descripcion',
        'Abreviatura',
        'Usr',
        'UsrFecha',
        'UsrHora'
    ];
    // Si no deseas utilizar timestamps, agrega la siguiente línea:
}
