<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadEconomica extends Model
{
    use HasFactory;
    protected $table = 'ACTIVIDADECONOMICA';
    protected $primaryKey = 'ActividadEconomica';
    public $timestamps = false; // La tabla no tiene timestamps
    protected $guarded = []; // O las propiedades que quieres proteger de asignación masiva


    protected $fillable = [
        'CodigoAnterior',
        'DescripcionActividad',
        'DescripcionComoUsar',
        'DescripcionDetallada',
        'TipoActividadEconomica',

        'ActividadEconomicaantiguo',
        'CodigoActividad',
    ];

}

