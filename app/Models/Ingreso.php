<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;

    protected $table = 'INGRESO';
    public $timestamps = false;
    protected $primaryKey = 'Ingreso';

    protected $fillable = [
        'Fecha',
        'Proveedor',
        'Usuario',
        'Costo',
        'Estado',
    ];
        // Relación con Producto
        public function proveedor()
        {
            return $this->belongsTo(Proveedor::class, 'Proveedor', 'Proveedor');
        }
        public function usuario()
        {
            return $this->belongsTo(User::class, 'Usuario', 'Usuario');
        }

}
