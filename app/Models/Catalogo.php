<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use HasFactory;

    protected $table = 'CATALOGO';
    protected $primaryKey = 'Catalogo';
    public $timestamps = false;

    protected $guarded = []; // O las propiedades que quieres proteger de asignación masiva

    protected $fillable = [
        'CodigoActividad',
        'CodigoNandina',
        'CodigoProducto',
        'DescripcionProducto',
        'FechaVigencia',
        'Usr',
        'UsrHora',
        'UsrFecha',
        'Estado'
    ];
}
