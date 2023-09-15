<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Descuento;

class DescuentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $descuentos = [
            [
                'Empresa' => 1,
                'Nombre' => 'Descuento A',
                'Tipo' => 1, //moneda
                'Valor' => 20.50,
            ],
            [
                'Empresa' => 2,
                'Nombre' => 'Descuento B',
                'Tipo' => 2,//Porcentaje
                'Valor' => 30.75,
            ],
            [
                'Empresa' => 1,
                'Nombre' => 'Descuento C',
                'Tipo' => 2,//Porcentaje
                'Valor' => 30.75,
            ],
            // Agrega más datos según sea necesario
        ];

        // Inserta los datos en la tabla 'DESCUENTO'
        foreach ($descuentos as $descuento) {
            Descuento::create($descuento);
        }
    }
}
