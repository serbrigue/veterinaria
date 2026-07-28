<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaEstadoActualizadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Cita $cita;

    public string $rol;

    /**
     * Create a new message instance.
     */
    public function __construct(Cita $cita, string $rol)
    {
        $this->cita = $cita;
        $this->rol = $rol;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de Cita Médica - '.config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.actualizada',
        );
    }

    public function attachments(): array
    {
        $adjuntos = [];

        // Asegurar que sabemos si tiene ficha clínica y cargos (lazy load si no venía)
        $this->cita->loadMissing([
            'fichaClinica',
            'cargos.insumo.categoriaInsumo',
            'prestacion',
            'transaccion'
        ]);

        // Si la cita está completada y tiene ficha clínica, adjuntamos el PDF
        if ($this->cita->estado === 'completada' && $this->cita->fichaClinica) {
            // Cargar relaciones necesarias para el PDF
            $this->cita->loadMissing([
                'fichaClinica.recetas', 
                'fichaClinica.vacunas', 
                'mascota.raza.especie', 
                'mascota.cliente.usuario', 
                'veterinario.usuario'
            ]);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ficha_clinica', [
                'cita' => $this->cita,
                'ficha' => $this->cita->fichaClinica
            ]);

            $adjuntos[] = \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'Ficha_Clinica_' . ($this->cita->mascota->nombre ?? 'Paciente') . '.pdf'
            )->withMime('application/pdf');
        }

        return $adjuntos;
    }
}
