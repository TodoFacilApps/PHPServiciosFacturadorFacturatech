<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    use HasFactory;

    protected $table = 'MONEDA';
    public $timestamps = false;
    protected $primaryKey = 'Moneda';

    protected $fillable = [
        'Nombre',
    ];

}
