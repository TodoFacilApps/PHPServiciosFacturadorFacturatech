<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'CREDITO';
    protected $primaryKey = 'Credito';
    public $timestamps = false;

    protected $guarded = []; // O las propiedades que quieres proteger de asignación masiva

    protected $fillable = [
        'Interes',
        'Empresa',
        'Cliente',
        'Cuotas',
        'CuotaMonto',
        'CuotaInicial',
        'Dias',
        'TasaInteres',
        'Estado',
        'User',
        'UserHr',
    ];
}
