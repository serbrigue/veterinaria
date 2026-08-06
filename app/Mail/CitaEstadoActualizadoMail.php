<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Attachment;

class CitaEstadoActualizadoMail extends Mailable implements ShouldQueue
{
    //Traits
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
            subject: 'Actualización de Cita Médica - ' . config('app.name'),
        );
    }

    //Método que permite configurar el contenido del correo
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.actualizada',
        );
    }

    //Método que permite configurar los archivos adjuntos del correo
    public function attachments(): array
    {
        //Inicializamos los adjuntos
        $adjuntos = [];

        //Aseguramos la carga de relaciones
        $this->cita->loadMissing([
            'fichaClinica',
            'cargos.insumo.categoriaInsumo',
            'prestacion',
            'transaccion'
        ]);

        //Si la cita está completada y tiene ficha clínica, adjuntamos el PDF
        if ($this->cita->estado === 'completada' && $this->cita->fichaClinica) {
            //Cargar relaciones necesarias para el PDF
            $this->cita->loadMissing([
                'fichaClinica.recetas',
                'fichaClinica.vacunas',
                'mascota.raza.especie',
                'mascota.cliente.usuario',
                'veterinario.usuario'
            ]);

            //Generamos el PDF
            $pdf = Pdf::loadView('pdf.ficha_clinica', [
                'cita' => $this->cita,
                'ficha' => $this->cita->fichaClinica
            ]);

            //Adjuntamos el PDF
            $adjuntos[] = Attachment::fromData(
                fn() => $pdf->output(),
                'Ficha_Clinica_' . ($this->cita->mascota->nombre ?? 'Paciente') . '.pdf'
            )->withMime('application/pdf');
        }

        return $adjuntos;
    }
}
