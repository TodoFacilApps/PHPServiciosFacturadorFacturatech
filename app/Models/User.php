<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject // Added here
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'USUARIO';
    protected $primaryKey = 'Usuario';
    public $timestamps = false;
    protected $hidden = ['password'];

    protected $fillable = [
        'Nombre',
        'Apellido',
        'email',
        'Correo',
        'password',
        'EmpresaSeleccionada',
        'Telefono'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

        // Relación con Empresa
        public function empresa()
        {
            return $this->belongsTo(Empresa::class, 'EmpresaSeleccionada', 'Empresa');
        }

}
