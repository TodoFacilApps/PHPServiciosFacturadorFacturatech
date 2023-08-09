<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'PRODUCTO';

    protected $primaryKey = 'Producto';
    public $timestamps = false;

    protected $fillable = [
        'Codigo',
        'Nombre',
        'ControlStock',
        'Stock',
    ];

}
