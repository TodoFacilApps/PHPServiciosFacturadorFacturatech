<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'PRODUCTO';

    protected $primaryKey = 'Producto';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Descripcion',
        'Empresa',
        'Estado',
        'TipoProducto',
        'ActividadEconomica',
        'CodigoProductoOrigen',
        'CatalogoImpuestos',
        'CodigoProductoEmpresa',
        'TipoProductoEmpresa',
        'UrlImagen',
        'Unidad',
        'Precio',
        'PrecioPorMayor',
        'PrecioOferta',
        'PrecioRemate',
        'NumeroOpciones',
        'Novedad',
        'Oferta',
        'NroVersion',
        'Posicion',
        'Saldo',
        'ControlaStock',
        'DecimalesCantidad',
        'MaximoStock',
        'Usr',
        'UsrHora',
        'UsrFecha',
        'ClaseSiat',
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'Unidad', 'UnidadMedida');
    }
}
