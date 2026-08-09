<?php

namespace Database\Seeders;

use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EventosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que las imágenes existan en el storage
        $this->verificarImagenes();

        // Obtener un organizador existente
        $organizadorId = 1;

        // ============================================================
        // EVENTO 1: Para mañana - EVENTO VIP
        // ============================================================
        Evento::create([
            'organizador_id' => $organizadorId,
            'nombre' => 'Noche de Gala VIP: Evento Exclusivo',
            'descripcion' => 'Una noche mágica con los mejores DJs, coctelería premium y un ambiente inigualable. Viste con elegancia para esta velada exclusiva. Evento VIP con acceso a zona premium y barra libre.',
            'fecha' => Carbon::now()->addDay()->toDateString(),
            'hora' => '21:00:00',
            'ciudad' => 'MADRID',
            'zona_ubicacion' => 'Centro, Calle Gran Vía 45',
            'ubicacion_lat' => 40.4203,
            'ubicacion_lng' => -3.7073,
            'precio' => 25.00,
            'capacidad' => 150,
            'tipo' => 'vip', // ✅ VIP
            'categoria' => 'Nocturna',
            'codigo_vestimenta' => 'Elegante',
            'estado' => 'publicado',
            'destacado' => true,
            'imagen' => 'eventos/EVENTO1.jpg',
            'metadatos' => [
                'duracion' => '6 horas',
                'edad_minima' => 18,
                'incluye' => ['Coctel de bienvenida', 'Espectáculo en vivo', 'Fotógrafo profesional', 'Zona VIP con barra libre'],
                'politicas' => 'No se permite entrada con ropa deportiva o casual',
                'acceso_vip' => true,
            ],
        ]);

        // ============================================================
        // EVENTO 2: Para la próxima semana - EVENTO GENERAL
        // ============================================================
        Evento::create([
            'organizador_id' => $organizadorId,
            'nombre' => 'Afterwork General: Networking y Diversión',
            'descripcion' => 'El mejor afterwork de la ciudad. Conecta con profesionales mientras disfrutas de música en vivo, aperitivos premium y un ambiente relajado. Evento abierto para todo el público.',
            'fecha' => Carbon::now()->addWeek()->toDateString(),
            'hora' => '19:30:00',
            'ciudad' => 'BARCELONA',
            'zona_ubicacion' => 'Distrito 22@, Carrer de la Innovació 12',
            'ubicacion_lat' => 41.3956,
            'ubicacion_lng' => 2.1908,
            'precio' => 15.00,
            'capacidad' => 200,
            'tipo' => 'general', // ✅ GENERAL
            'categoria' => 'Social',
            'codigo_vestimenta' => 'Smart casual',
            'estado' => 'publicado',
            'destacado' => false,
            'imagen' => 'eventos/EVENTO2.jpg',
            'metadatos' => [
                'duracion' => '4 horas',
                'edad_minima' => 18,
                'incluye' => ['Aperitivo', 'Bebida de cortesía', 'Networking estructurado', 'Música en vivo'],
                'politicas' => 'Se requiere registro previo para acceder al networking',
                'acceso_vip' => false,
            ],
        ]);

        $this->command->info('✅ Eventos creados exitosamente:');
        $this->command->info('📅 Evento 1 VIP: Mañana - ' . Carbon::now()->addDay()->format('d/m/Y'));
        $this->command->info('📅 Evento 2 GENERAL: Próxima semana - ' . Carbon::now()->addWeek()->format('d/m/Y'));
        $this->command->info('📸 Imágenes esperadas en: storage/app/public/eventos/');
    }

    /**
     * Verificar que las imágenes existan en el storage
     */
    private function verificarImagenes(): void
    {
        $imagenes = ['EVENTO1.jpg', 'EVENTO2.jpg'];
        $directorio = storage_path('app/public/eventos');

        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
            $this->command->info('📁 Directorio creado: ' . $directorio);
        }

        foreach ($imagenes as $imagen) {
            $ruta = $directorio . '/' . $imagen;
            
            if (!file_exists($ruta)) {
                $this->command->warn("⚠️ La imagen no existe: {$ruta}");
                $this->command->warn("📌 Coloca la imagen en: {$ruta}");
            } else {
                $this->command->info("✅ Imagen encontrada: {$imagen}");
            }
        }
    }

    /**
     * Obtener un ID de organizador válido
     */
    private function getOrganizadorId(): int
    {
        $admin = \App\Models\Administrador::first();

        if ($admin) {
            return $admin->id;
        }

        $user = \App\Models\User::where('rol', 'admin')->first();
        
        if ($user) {
            $admin = \App\Models\Administrador::create([
                'usuario_id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'permisos' => ['crear_eventos', 'gestionar_reservas', 'ver_reportes'],
            ]);
            
            $this->command->info('✅ Administrador creado automáticamente para el usuario: ' . $user->nombre);
            return $admin->id;
        }

        $user = \App\Models\User::create([
            'nombre' => 'Admin Eventos',
            'apodo' => 'admin_eventos',
            'email' => 'admin@eventos.com',
            'password' => bcrypt('admin123'),
            'rol' => 'admin',
            'estado' => 'verificado',
        ]);

        $admin = \App\Models\Administrador::create([
            'usuario_id' => $user->id,
            'nombre' => 'Admin Eventos',
            'email' => 'admin@eventos.com',
            'permisos' => ['crear_eventos', 'gestionar_reservas', 'ver_reportes'],
        ]);

        $this->command->info('✅ Administrador creado: admin@eventos.com / admin123');
        
        return $admin->id;
    }
}