<?php

namespace App\Observers;

use App\Models\Cita;
use App\Mail\CitaAgendadaMail;
use App\Mail\CitaCanceladaMail;
use App\Mail\CitaEstadoActualizadoMail;
use Illuminate\Support\Facades\Mail;

class CitaObserver
{
    /**
     * Handle the Cita "created" event.
     */
    public function created(Cita $cita): void
    {
        // Cargar las relaciones necesarias para las plantillas de correo
        $cita->loadMissing(['mascota.cliente.usuario', 'veterinario.usuario', 'box.sucursal']);

        $clienteEmail = $cita->mascota->cliente->usuario->email ?? null;
        $veterinarioEmail = $cita->veterinario->usuario->email ?? null;

        if ($clienteEmail) {
            Mail::to($clienteEmail)->send(new CitaAgendadaMail($cita, 'cliente'));
        }

        if ($veterinarioEmail) {
            Mail::to($veterinarioEmail)->send(new CitaAgendadaMail($cita, 'veterinario'));
        }
    }

    /**
     * Handle the Cita "updated" event.
     */
    public function updated(Cita $cita): void
    {
        if ($cita->isDirty('estado')) {
            $cita->loadMissing(['mascota.cliente.usuario', 'veterinario.usuario', 'box.sucursal']);
            
            if ($cita->estado === 'completada') {
                $cita->loadMissing(['transaccion', 'prestacion', 'cargos.insumo']);
            }
            
            $clienteEmail = $cita->mascota->cliente->usuario->email ?? null;
            $veterinarioEmail = $cita->veterinario->usuario->email ?? null;

            if ($cita->estado === 'cancelada') {
                if ($clienteEmail) {
                    Mail::to($clienteEmail)->send(new CitaCanceladaMail($cita, 'cliente'));
                }
                if ($veterinarioEmail) {
                    Mail::to($veterinarioEmail)->send(new CitaCanceladaMail($cita, 'veterinario'));
                }
            } else {
                if ($clienteEmail) {
                    Mail::to($clienteEmail)->send(new CitaEstadoActualizadoMail($cita, 'cliente'));
                }
                if ($veterinarioEmail) {
                    Mail::to($veterinarioEmail)->send(new CitaEstadoActualizadoMail($cita, 'veterinario'));
                }
            }
        }
    }
}
