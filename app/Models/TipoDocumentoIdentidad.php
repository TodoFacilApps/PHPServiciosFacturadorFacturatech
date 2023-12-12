<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentoIdentidad extends Model
{
    use HasFactory;

    protected $table = 'TIPODOCUMENTOIDENTIDAD';

    protected $primaryKey = 'TipoDocumentoIdentidad';

    protected $fillable = [
        'Nombre',
        'Sigla',
        'Posicion',
        'UrlImagen',
        'Usr',
        'UsrHora',
        'UsrFecha',
    ];
}
