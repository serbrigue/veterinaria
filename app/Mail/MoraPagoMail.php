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
    //Traits
    use Queueable, SerializesModels;

    //Propiedades
    public Cliente $cliente;
    public Collection $transacciones;

    //Constructor
    public function __construct(Cliente $cliente, Collection $transacciones)
    {
        $this->cliente = $cliente;
        $this->transacciones = $transacciones;
    }

    //Método que permite configurar el sobre del correo
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso de Saldo Pendiente - Veterinaria',
        );
    }

    //Método que permite configurar el contenido del correo
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pagos.mora',
        );
    }

    //Método que permite configurar los archivos adjuntos del correo
    public function attachments(): array
    {
        return [];
    }
}
