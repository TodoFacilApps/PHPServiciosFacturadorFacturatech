<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpuestoIva extends Model
{
    use HasFactory;

    protected $table = 'IMPUESTOIVA';
    public $timestamps = false;
    protected $primaryKey = 'ImpuestoIVA';

    protected $fillable = [
        'Porcentaje',
        'Valor',
];


}
