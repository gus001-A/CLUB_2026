<?php

namespace App\Mail;

use App\Models\CodigoInvitacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionRegistroMail extends Mailable
{
    use Queueable, SerializesModels;

    public CodigoInvitacion $invitacion;

    /**
     * OJO: recibimos el modelo completo (no solo el código) porque la
     * plantilla necesita nombre_destinatario, tipo, vigencia y el mensaje
     * personalizado que el admin escribió en Create.vue.
     */
    public function __construct(CodigoInvitacion $invitacion)
    {
        $this->invitacion = $invitacion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu invitación a Club de Fantasías',
        );
    }

    public function content(): Content
    {
        $tipoLabel = [
            'registro' => 'Registro',
            'premium' => 'Premium',
            'evento' => 'Evento',
        ][$this->invitacion->metadata['tipo'] ?? 'registro'] ?? 'Registro';

        return new Content(
            view: 'emails.invitacion',
            with: [
                'nombre' => $this->invitacion->nombre_destinatario,
                'codigo' => $this->invitacion->codigo,
                'tipoLabel' => $tipoLabel,
                'mensaje' => $this->invitacion->metadata['mensaje'] ?? null,
                'expiraEn' => $this->invitacion->expira_en,
                // route() agrega 'codigo' como query string ya que no es un
                // parámetro de la ruta — así el link llega con el código
                // precargado y la persona solo confirma sus datos.
                'urlRegistro' => route('register.invite', ['codigo' => $this->invitacion->codigo]),
            ],
        );
    }
}