<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        /**
         * Para poder restapleser la secuencia del auto incrementable del identificador
         *(nombre de la sacuencia de la tabla(se puede encontrar en los valores defalult del identificador),
         * numero al cual que quiere apuntar para seguir esa secuencia desde ese punto ,
         *no estoy bien segura pero se afirma con true para ejecutar la sentencia)
         *##-SELECT setval('"PRODUCTO_Producto_seq"', 14666, true);
         */
    }
}
