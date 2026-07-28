<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Veterinario;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function editar(Request $request): Response
    {

        // Obtenemos el id del cliente
        $clienteId = auth()->user()->cliente?->id;
        // Obtenemos el id del veterinario
        $veterinarioId = auth()->user()->veterinario?->id;

        // Obtenemos la mascota
        $mascota = Mascota::where('cliente_id', $clienteId)->first();
        // Obtenemos el veterinario
        $veterinario = auth()->user()->veterinario;

        // Obtenemos las proximas citas
        $proximasCitas = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->where('estado', '!=', 'cancelada')->where('fecha_hora', '>=', now())->first();

        // Obtenemos el historial clinico
        $historialClinico = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])
            ->where('estado', '=', 'completada')
            ->where('fecha_hora', '<', now())
            ->orderBy('fecha_hora', 'desc')
            ->first();

        // Obtenemos la proxima cita del veterinario
        $proximaCitaVet = Cita::where('veterinario_id', $veterinarioId)
            ->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])
            ->where('estado', '!=', 'cancelada')
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora', 'asc')
            ->first();

        $cotizaciones = [];
        if ($veterinario) {
            $pagosRegistrados = \App\Models\PagoVeterinario::where('veterinario_id', $veterinario->id)->get()->keyBy(function($item) {
                return $item->anio . '-' . $item->mes;
            });

            $citasCompletadas = $veterinario->citas()
                ->where('estado', 'completada')
                ->whereHas('transaccion', function ($t) {
                    $t->where('estado', 'pagado');
                })
                ->with(['prestacion', 'transaccion'])
                ->get();

            foreach ($citasCompletadas as $cita) {
                $fecha = \Carbon\Carbon::parse($cita->transaccion->fecha_pago);
                $key = $fecha->format('Y-n');
                if (!isset($cotizaciones[$key])) {
                    $cotizaciones[$key] = [
                        'mes' => $fecha->month,
                        'anio' => $fecha->year,
                        'mes_nombre' => ucfirst($fecha->translatedFormat('F Y')),
                        'citas_count' => 0,
                        'monto_generado' => 0,
                        'comision_calculada' => 0,
                        'estado' => isset($pagosRegistrados[$key]) ? $pagosRegistrados[$key]->estado : 'pendiente',
                        'pago_id' => isset($pagosRegistrados[$key]) ? $pagosRegistrados[$key]->id : null
                    ];
                }
                $cotizaciones[$key]['citas_count']++;
                $precioBase = $cita->prestacion ? $cita->prestacion->precio_base : 0;
                $porcentaje = $cita->prestacion ? $cita->prestacion->comision_vet : 0;
                $cotizaciones[$key]['monto_generado'] += $precioBase;
                $cotizaciones[$key]['comision_calculada'] += ($precioBase * $porcentaje) / 100;
            }

            usort($cotizaciones, function($a, $b) {
                return ($b['anio'] <=> $a['anio']) ?: ($b['mes'] <=> $a['mes']);
            });
        }

        // Devolvemos la vista con todos los datos
        return Inertia::render('Perfil/Editar', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'proximasCitas' => $proximasCitas,
            'historialClinico' => $historialClinico,
            'proximaCitaVet' => $proximaCitaVet,
            'mascota' => $mascota,
            'veterinario' => $veterinario,
            'cotizaciones' => $cotizaciones,
        ]);
    }

    public function actualizar(ProfileUpdateRequest $request): RedirectResponse
    {
        // Actualizamos el perfil
        $request->user()->fill($request->validated());
        // Si el email ha cambiado, actualizamos la fecha de verificacion
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        // Guardamos los cambios

        $request->user()->save();

        return Redirect::route('perfil.editar');
    }

    public function eliminar(Request $request): RedirectResponse
    {

        // Validamos la contraseña
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        // Obtenemos el usuario
        $user = $request->user();

        // Cerramos la sesion
        Auth::logout();
        // Eliminamos el usuario
        $user->delete();
        // Invalidamos la sesion
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function actualizarApi(ProfileUpdateRequest $solicitud)
    {
        // Actualizamos el perfil
        $solicitud->user()->fill($solicitud->validated());
        // Si el email ha cambiado, actualizamos la fecha de verificacion
        if ($solicitud->user()->isDirty('email')) {
            $solicitud->user()->email_verified_at = null;
        }
        // Guardamos los cambios
        $solicitud->user()->save();

        return response()->json(['mensaje' => 'Perfil actualizado']);
    }

    public function actualizarContrasenaApi(Request $solicitud)
    {
        // Validamos la contraseña
        $solicitud->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Actualizamos la contraseña

        $solicitud->user()->update([
            'password' => Hash::make($solicitud->password),
        ]);

        return response()->json(['mensaje' => 'Contraseña actualizada']);
    }

    public function eliminarApi(Request $solicitud)
    {
        // Validamos la contraseña
        $solicitud->validate([
            'password' => ['required', 'current_password'],
        ]);
        // Obtenemos el usuario
        $usuario = $solicitud->user();

        // Cerramos la sesion
        Auth::logout();
        // Eliminamos el usuario
        $usuario->delete();
        // Invalidamos la sesion
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return response()->json(['redirect' => '/']);
    }
}
