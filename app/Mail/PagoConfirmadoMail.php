<?php

namespace App\Mail;

use App\Models\Transaccion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagoConfirmadoMail extends Mailable implements ShouldQueue
{
    //Traits
    use Queueable, SerializesModels;

    //Propiedades
    public Transaccion $transaccion;

    //Constructor
    public function __construct(Transaccion $transaccion)
    {
        $this->transaccion = $transaccion;
    }

    //Método que permite configurar el sobre del correo
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprobante de Pago - ' . config('app.name'),
        );
    }

    //Método que permite configurar el contenido del correo
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pagos.confirmacion',
        );
    }

    //Método que permite configurar los archivos adjuntos del correo
    public function attachments(): array
    {
        return [];
    }
}
