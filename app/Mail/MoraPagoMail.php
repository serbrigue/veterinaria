<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

class MoraPagoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Cliente $cliente;
    public Collection $transacciones;

    /**
     * Create a new message instance.
     */
    public function __construct(Cliente $cliente, Collection $transacciones)
    {
        $this->cliente = $cliente;
        $this->transacciones = $transacciones;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso de Saldo Pendiente - Veterinaria',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pagos.mora',
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
