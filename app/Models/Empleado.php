<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'PERSONAL';
    protected $primaryKey = 'Empleado'; // Si el campo de la clave primaria no es 'id'
    public $timestamps = false; // No necesitas los campos de fecha de creación y actualización

    protected $fillable = [
        'Nombre',
        'Apellido',
        'Documento',
        'Cargo',
        'EmpresaSucursal',
        'Telefono',
        'Usr',
        'UsrFecha',
        'UsrHora',
        'email', // Si deseas permitir la modificación del correo electrónico
        'password'
    ];

    protected $dates = ['UsrFecha']; // Si deseas que 'UsrFecha' se maneje como una instancia de Carbon

    protected $hidden = [
        'password', 'remember_token'
    ];

    // Puedes definir relaciones aquí si es necesario, por ejemplo:
    // public function cargo()
    // {
    //     return $this->belongsTo(Cargo::class, 'Cargo');
    // }

    public function empresaSucursal()
     {
         return $this->belongsTo(EmpresaSucursal::class, 'EmpresaSucursal');
     }
}
