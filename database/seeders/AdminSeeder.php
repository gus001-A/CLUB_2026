<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Credenciales de prueba:
     * email: admin@clubdefantasias.com
     * password: Admin1234
     */
    public function run(): void
    {
        Administrador::updateOrCreate(
            ['email' => 'admin@clubdefantasias.com'],
            [
                'nombre' => 'Super Admin',
                'password' => 'Admin1234', // el mutador del modelo lo encripta solo
                'rol' => 'super_admin',
                'esta_activo' => true,
                'email_verificado_en' => now(),
            ]
        );
    }
}