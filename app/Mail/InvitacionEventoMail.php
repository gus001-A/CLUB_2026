<?php

namespace App\Mail;

use App\Models\CodigoInvitacion;
use App\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionEventoMail extends Mailable
{
    use Queueable, SerializesModels;

    public CodigoInvitacion $invitacion;
    public Evento $evento;

    public function __construct(CodigoInvitacion $invitacion, Evento $evento)
    {
        $this->invitacion = $invitacion;
        $this->evento = $evento;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Estás invitado a {$this->evento->nombre} — Club de Fantasías",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitacion-evento',
            with: [
                'nombre' => $this->invitacion->nombre_destinatario,
                'mensaje' => $this->invitacion->metadata['mensaje'] ?? null,
                'eventoNombre' => $this->evento->nombre,
                'eventoFecha' => $this->evento->fecha_formateada,
                'eventoHora' => $this->evento->hora_formateada,
                'eventoCiudad' => $this->evento->ciudad,
                'eventoZona' => $this->evento->zona_ubicacion,
                'eventoImagen' => $this->evento->imagen_url,
                // Es un usuario que ya existe en el sistema — no hace falta
                // código de invitación ni registro, solo llevarlo a la
                // ficha del evento al que lo invitaron.
                'urlEvento' => route('eventos.show', $this->evento->id),
            ],
        );
    }
}