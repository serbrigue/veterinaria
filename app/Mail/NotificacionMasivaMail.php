<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionMasivaMail extends Mailable implements ShouldQueue
{
    //Traits
    use Queueable, SerializesModels;

    //Propiedades
    public string $asunto;
    public string $mensaje;
    public string $clienteNombre;

    //Constructor
    public function __construct(string $asunto, string $mensaje, string $clienteNombre)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->clienteNombre = $clienteNombre;
    }

    //Método que permite configurar el sobre del correo
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    //Método que permite configurar el contenido del correo
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notificacion_masiva',
        );
    }

    //Método que permite configurar los archivos adjuntos del correo
    public function attachments(): array
    {
        return [];
    }
}
