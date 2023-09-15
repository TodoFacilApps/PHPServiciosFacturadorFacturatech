<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificacionTipoDocumentoSector extends Model
{
    use HasFactory;

    protected $table = 'CERTIFICACIONTIPODOCUMENTOSECTOR';

    protected $primaryKey = ['Certificacion', 'Serial'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'TipoDocumentoSector'
    ];

}
