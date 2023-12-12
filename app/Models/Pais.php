<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;
    protected $table = 'PAIS';
    protected $primaryKey = 'Pais';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Code2',
        'Code3',
        'CodigoTelefonico',
        'Prioridad',
        'PedirCodigoPostal',
        'ZonaHoraria',
        'Idioma',
        'Estado',
        'Moneda',
        'Codigo',
        'Simbolo',
        'Usr',
        'UsrFecha',
        'UsrHora'
    ];

    protected $casts = [
        'Estado' => 'integer', // Para tratar el campo 'Estado' como entero
    ];

    protected $dates = [
        'UsrFecha',
    ];
}
