<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaseSiat extends Model
{
    use HasFactory;

    protected $table = 'CLASESIAT';
    protected $primaryKey = 'ClaseSIAT';
    public $timestamps = false; // La tabla no tiene timestamps
    protected $guarded = []; // O las propiedades que quieres proteger de asignación masiva

    // Si los nombres de las columnas no siguen las convenciones de Laravel, define
    // las propiedades para mapear correctamente
     protected $attributes = [
         'ClaseSIAT' => 'ClaseSIAT',
         'Nombre' => 'Nombre',
         'Usr' => 'Usr',
         'UsrHora' => 'UsrHora',
         'UsrFecha' => 'UsrFecha',
         'Campo' => 'Campo'
     ];

}
