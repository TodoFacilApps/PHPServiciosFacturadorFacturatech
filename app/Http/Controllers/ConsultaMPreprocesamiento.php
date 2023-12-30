<?php

namespace App\Preprocessing;

use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder;

class DataPreprocessor
{
    public function cleanData(Builder $query)
    {
        // Eliminamos los valores at�picos
        $query->whereNotNull('age');
        $query->where('age', '<', 120);
        // Eliminamos los errores
        $query->whereNotNull('name');
        $query->where('name', '!=', '');
        // Eliminamos los datos faltantes
        $query->whereNotNull('gender');
    }

    public function transformData(Builder $query)
    {
        // Cambiamos el formato de la fecha
        $query->whereDate('birthdate', '>=', '1970-01-01');
        $query->whereDate('birthdate', '<=', '2000-12-31');

        // Convertimos las categor�as a n�meros
        $query->whereIn('gender', ['male', 'female']);
    }

    public function reduceDimensionality(Builder $query)
    {
        // Eliminamos las caracter�sticas no relevantes
        $query->select('age', 'gender');
    }
}