<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Códigos de verificación de correo de 6 dígitos, compartidos entre el
 * registro por invitación y el login (para usuarios que un admin crea
 * directo desde el panel). Usa las MISMAS claves de caché que ya estaban
 * en InviteRegisterController — no lo toqué para no arriesgar ese flujo,
 * que ya quedó funcionando.
 */
class CodigoVerificacion
{
    public static function generar(string $email): string
    {
        $codigo = (string) random_int(100000, 999999);
        Cache::put(self::claveCodigo($email), $codigo, now()->addMinutes(10));

        return $codigo;
    }

    /**
     * ¿Ya hay un código sin usar y sin expirar para este correo? Sirve
     * para no pisar (e invalidar sin querer) un código que ya se mandó
     * y que la persona todavía no ha tenido oportunidad de usar — por
     * ejemplo, el que va en el correo de bienvenida cuando un admin
     * crea la cuenta, que debe seguir sirviendo aunque pasen los 45s
     * de cooldown de "reenviar" antes de su primer intento de login.
     */
    public static function existeVigente(string $email): bool
    {
        return Cache::has(self::claveCodigo($email));
    }

    public static function valido(string $email, string $codigo): bool
    {
        $guardado = Cache::get(self::claveCodigo($email));

        return $guardado !== null && hash_equals((string) $guardado, (string) $codigo);
    }

    public static function olvidar(string $email): void
    {
        Cache::forget(self::claveCodigo($email));
    }

    public static function puedeReenviar(string $email): bool
    {
        return ! Cache::has(self::claveThrottle($email));
    }

    public static function marcarEnviado(string $email): void
    {
        Cache::put(self::claveThrottle($email), true, now()->addSeconds(45));
    }

    protected static function claveCodigo(string $email): string
    {
        return 'verificacion_email_codigo:' . strtolower($email);
    }

    protected static function claveThrottle(string $email): string
    {
        return 'verificacion_email_throttle:' . strtolower($email);
    }
}