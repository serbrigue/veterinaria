<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaAgendadaMail extends Mailable implements ShouldQueue
{
    //Traits que permiten el uso de colas y serialización
    use Queueable, SerializesModels;

    //Propiedades
    public Cita $cita;

    //Propiedad
    public string $rol;

    //Constructor
    public function __construct(Cita $cita, string $rol)
    {
        $this->cita = $cita;
        $this->rol = $rol;
    }

    //Método que permite configurar el sobre del correo
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cita Médica Agendada - '.config('app.name'),
        );
    }

    //Método que permite configurar el contenido del correo
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.agendada',
        );
    }

    //Método que permite configurar los archivos adjuntos del correo
    public function attachments(): array
    {
        return [];
    }
}
