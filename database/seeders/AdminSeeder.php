<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Credenciales de prueba:
     * nickname: admin001
     * email: admin@clubdefantasias.com
     * password: Admin1234
     */
    public function run(): void
    {
        Administrador::updateOrCreate(
            ['email' => 'admin@clubdefantasias.com'],
            [
                'nombre' => 'Super Admin',
                'nickname' => 'admin001',
                'password' => 'Admin1234', 
                'esta_activo' => true,
                'email_verificado_en' => now(),
            ]
        );
    }
}