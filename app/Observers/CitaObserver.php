<?php

namespace App\Observers;

use App\Models\Cita;
use App\Mail\CitaAgendadaMail;
use App\Mail\CitaCanceladaMail;
use App\Mail\CitaEstadoActualizadoMail;
use Illuminate\Support\Facades\Mail;

class CitaObserver
{
    #Metodo que se ejecuta cuando se crea una cita
    public function created(Cita $cita): void
    {
        #Cargar las relaciones necesarias para las plantillas de correo
        $cita->loadMissing(['mascota.cliente.usuario', 'veterinario.usuario', 'box.sucursal']);

        #Obtener el email del cliente
        $clienteEmail = $cita->mascota->cliente->usuario->email ?? null;

        #Obtener el email del veterinario
        $veterinarioEmail = $cita->veterinario->usuario->email ?? null;

        #Enviar correo al cliente
        if ($clienteEmail) {
            Mail::to($clienteEmail)->send(new CitaAgendadaMail($cita, 'cliente'));
        }

        #Enviar correo al veterinario
        if ($veterinarioEmail) {
            Mail::to($veterinarioEmail)->send(new CitaAgendadaMail($cita, 'veterinario'));
        }
    }

    #Metodo que se ejecuta cuando se actualiza una cita
    public function updated(Cita $cita): void
    {
        #Si cambia el estado
        if ($cita->isDirty('estado')) {
            #Cargar las relaciones necesarias
            $cita->loadMissing(['mascota.cliente.usuario', 'veterinario.usuario', 'box.sucursal']);

            #Si se completa la cita
            if ($cita->estado === 'completada') {
                $cita->loadMissing(['transaccion', 'prestacion', 'cargos.insumo']);
            }

            #Obtener el email del cliente
            $clienteEmail = $cita->mascota->cliente->usuario->email ?? null;

            #Obtener el email del veterinario
            $veterinarioEmail = $cita->veterinario->usuario->email ?? null;

            #Si se cancela la cita
            if ($cita->estado === 'cancelada') {
                #Enviar correo al cliente
                if ($clienteEmail) {
                    Mail::to($clienteEmail)->send(new CitaCanceladaMail($cita, 'cliente'));
                }
                #Enviar correo al veterinario
                if ($veterinarioEmail) {
                    Mail::to($veterinarioEmail)->send(new CitaCanceladaMail($cita, 'veterinario'));
                }
            } else {
                #Enviar correo al cliente
                if ($clienteEmail) {
                    Mail::to($clienteEmail)->send(new CitaEstadoActualizadoMail($cita, 'cliente'));
                }
                #Enviar correo al veterinario
                if ($veterinarioEmail) {
                    Mail::to($veterinarioEmail)->send(new CitaEstadoActualizadoMail($cita, 'veterinario'));
                }
            }
        }
    }
}
