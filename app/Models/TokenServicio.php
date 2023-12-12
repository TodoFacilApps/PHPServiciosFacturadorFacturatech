<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenServicio extends Model
{
    use HasFactory;
    protected $table = 'TOKENSERVICIO';
    protected $primaryKey = 'TokenServicio';
    public $timestamps = false;

    protected $fillable = [
        'ApiToken',
        'TokenService',
        'TokenSecret',
        'TokenBearer',
        'Empresa',
    ];
}
