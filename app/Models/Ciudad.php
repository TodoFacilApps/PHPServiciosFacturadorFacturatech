<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'CIUDAD';
    protected $primaryKey = 'Ciudad';
    public $timestamps = false;

    protected $guarded = []; // O las propiedades que quieres proteger de asignación masiva

    protected $fillable = [
        'Ciudad',
        'Nombre',
        'Estado',
        'Abreviatura',
        'Posicion',
        'Latitud',
        'Longitud',
        'Habilitado',
        'Usr',
        'UsrHora',
        'UsrFecha'
    ];
}
