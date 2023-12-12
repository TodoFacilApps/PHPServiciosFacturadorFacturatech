<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    use HasFactory;

    protected $table = 'TIPOMONEDA';
    public $timestamps = false;
    protected $primaryKey = 'CodigoClasificador';

    protected $fillable = [
        'Descripcion',
        'Sigla',
        'Simbolo',
    ];

}
