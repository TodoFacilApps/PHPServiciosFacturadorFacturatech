<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsociacionTipoDocumentoSector extends Model
{
    use HasFactory;
    protected $table = 'ASOCIACIONTIPODOCUMENTOSECTOR';

    protected $primaryKey = ['Asociacion', 'Serial', 'TipoDocumentoSector'];

    public $incrementing = false;

    protected $keyType = 'integer';

    public $timestamps = false;

    protected $fillable = [
        'Asociacion',
        'Serial',
        'TipoDocumentoSector',
        'Habilitado',
    ];
}
