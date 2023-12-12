<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaCategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'EMPRESACATEGORIAPRODUCTO';
    protected $primaryKey = 'EmpresaCategoriaProducto';
    public $timestamps = false;
    protected $fillable = ['Empresa', 'Nombre', 'Valor'];
}
