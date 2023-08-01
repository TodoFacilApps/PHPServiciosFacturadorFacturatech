<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\User as Authenticatable;


//use Tymon\JWTAuth\Contracts\JWTSubject; // Add this line
use Tymon\JWTAuth\Contracts\JWTSubject;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class Usuario extends Authenticatable  implements JWTSubject // Added here
{
    use HasFactory;
    use HasApiTokens;

    protected $table = 'USUARIO';
    protected $primaryKey = 'Usuario';
    public $timestamps = false;
//    protected $hidden = ['password'];

    protected $fillable = [
        'Nombre',
        'Apellido',
        'email',
        'Correo',
        'password',
        'Telefono'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
