<?php

namespace Tests\Unit;

use App\Models\CodigoInvitacion;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Cubre la lógica de validación de CodigoInvitacion (esValido() y sus
 * piezas: expiración, uso y límite de usos). Esta lógica decide si una
 * invitación deja o no entrar a un usuario nuevo, así que un regresión
 * aquí es silenciosa hasta que alguien queda bloqueado (o alguien entra
 * con un código que ya no debería servir).
 *
 * Los modelos se construyen en memoria sin guardarlos en base de datos:
 * esValido() y sus dependencias sólo leen atributos, no consultan la BD.
 */
class CodigoInvitacionTest extends TestCase
{
    public function test_es_valido_cuando_esta_activo_sin_expirar_y_sin_usar(): void
    {
        $codigo = new CodigoInvitacion([
            'esta_activo' => true,
            'expira_en' => Carbon::now()->addDays(5),
            'usado_en' => null,
            'usos_maximos' => 1,
            'contador_usos' => 0,
        ]);

        $this->assertTrue($codigo->esValido());
    }

    public function test_no_es_valido_si_esta_inactivo(): void
    {
        $codigo = new CodigoInvitacion([
            'esta_activo' => false,
            'expira_en' => Carbon::now()->addDays(5),
            'usado_en' => null,
            'usos_maximos' => 1,
            'contador_usos' => 0,
        ]);

        $this->assertFalse($codigo->esValido());
    }

    public function test_esta_expirado_cuando_la_fecha_de_expiracion_ya_paso(): void
    {
        $codigo = new CodigoInvitacion([
            'expira_en' => Carbon::now()->subDay(),
        ]);

        $this->assertTrue($codigo->estaExpirado());
        $this->assertFalse($codigo->esValido());
    }

    public function test_no_esta_expirado_cuando_no_tiene_fecha_de_expiracion(): void
    {
        $codigo = new CodigoInvitacion([
            'expira_en' => null,
        ]);

        $this->assertFalse($codigo->estaExpirado());
    }

    public function test_ha_alcanzado_usos_maximos_cuando_el_contador_los_iguala(): void
    {
        $codigo = new CodigoInvitacion([
            'usos_maximos' => 3,
            'contador_usos' => 3,
        ]);

        $this->assertTrue($codigo->haAlcanzadoUsosMaximos());
    }

    public function test_no_ha_alcanzado_usos_maximos_cuando_quedan_usos_disponibles(): void
    {
        $codigo = new CodigoInvitacion([
            'usos_maximos' => 3,
            'contador_usos' => 1,
        ]);

        $this->assertFalse($codigo->haAlcanzadoUsosMaximos());
    }

    public function test_etiqueta_estado_refleja_prioridad_inactivo_sobre_expirado(): void
    {
        // Un código inactivo debe mostrarse como "Inactivo" incluso si
        // además ya expiró: esValido() comprueba esta_activo primero.
        $codigo = new CodigoInvitacion([
            'esta_activo' => false,
            'expira_en' => Carbon::now()->subDay(),
            'usado_en' => null,
            'usos_maximos' => 1,
            'contador_usos' => 0,
        ]);

        $this->assertSame('Inactivo', $codigo->etiqueta_estado);
    }

    public function test_etiqueta_estado_disponible_cuando_todo_esta_en_orden(): void
    {
        $codigo = new CodigoInvitacion([
            'esta_activo' => true,
            'expira_en' => Carbon::now()->addDays(5),
            'usado_en' => null,
            'usos_maximos' => 1,
            'contador_usos' => 0,
        ]);

        $this->assertSame('Disponible', $codigo->etiqueta_estado);
    }

    public function test_porcentaje_de_uso_se_calcula_sobre_usos_maximos(): void
    {
        $codigo = new CodigoInvitacion([
            'usos_maximos' => 4,
            'contador_usos' => 1,
        ]);

        $this->assertSame(25.0, $codigo->porcentaje_uso);
    }

    public function test_porcentaje_de_uso_es_cero_si_usos_maximos_no_es_positivo(): void
    {
        $codigo = new CodigoInvitacion([
            'usos_maximos' => 0,
            'contador_usos' => 0,
        ]);

        $this->assertSame(0.0, $codigo->porcentaje_uso);
    }
}
