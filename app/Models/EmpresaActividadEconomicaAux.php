<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaActividadEconomicaAux extends Model
{
    use HasFactory;

    protected $table = 'EMPRESAACTIVIDADECONOMICAAUX';

    protected $primaryKey = ['Empresa', 'ActividadEconomica'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Estado'
    ];

    // Otros atributos y métodos del modelo

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'Empresa', 'Empresa');
    }
}
