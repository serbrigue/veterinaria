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
    use Queueable, SerializesModels;

    public string $asunto;
    public string $mensaje;
    public string $clienteNombre;

    /**
     * Create a new message instance.
     */
    public function __construct(string $asunto, string $mensaje, string $clienteNombre)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->clienteNombre = $clienteNombre;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notificacion_masiva',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
