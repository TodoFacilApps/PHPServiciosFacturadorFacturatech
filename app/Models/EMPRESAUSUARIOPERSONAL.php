<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMPRESAUSUARIOPERSONAL extends Model
{
    use HasFactory;

    protected $table = 'EMPRESAUSUARIOPERSONAL';
    protected $primaryKey = ['Empresa', 'Serial'];
    public $incrementing = false;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'Empresa',
        'Serial',
        'Usuario',
        'Estado',
    ];

    // Relación con la tabla EMPRESA
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'Empresa', 'Empresa');
    }
}
