<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $usuario;
    public string $passwordPlano;
    public string $codigo;

    /**
     * OJO: $passwordPlano tiene que venir de ANTES de Hash::make() en el
     * controller — una vez hasheada ya no hay forma de recuperarla.
     */
    public function __construct(User $usuario, string $passwordPlano, string $codigo)
    {
        $this->usuario = $usuario;
        $this->passwordPlano = $passwordPlano;
        $this->codigo = $codigo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cuenta en Club de Fantasías',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-usuario',
            with: [
                'nombre' => $this->usuario->nombre,
                'apodo' => $this->usuario->apodo,
                'password' => $this->passwordPlano,
                'codigo' => $this->codigo,
                'urlLogin' => route('login'),
            ],
        );
    }
}