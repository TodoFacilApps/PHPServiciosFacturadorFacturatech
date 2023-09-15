<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'DESCUENTO';

    protected $fillable = [
        'Empresa',
        'Nombre',
        'Tipo',
        'Valor',
    ];

    protected $primaryKey = 'Descuento';

    public $timestamps = false;
}
