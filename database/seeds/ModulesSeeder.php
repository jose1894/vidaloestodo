<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Modules;
use Carbon\Carbon;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Borramos todos los mudlos registrados
        Modules::truncate();

        //Registramos los nuevos
        Modules::insert([
            [
                'name' => 'Usuarios',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Producto',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Promoción',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Pagos',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Pedidos',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Tickets de soporte',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Tasa del día',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Reporte',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Contenidos',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'SEO',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Pasarelas',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Métodos de Envio',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Ajustes',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Correo',
                'status' => '1',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Limpiar Caché',
                'status' => '1',
                'created_at' => Carbon::now(),
            ]
        ]);
    }
}
