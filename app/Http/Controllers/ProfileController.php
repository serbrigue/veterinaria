<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Veterinario;


class ProfileController extends Controller
{
    public function editar(Request $request): Response
    {

        #Obtenemos el id del cliente
        $clienteId = auth()->user()->cliente?->id;
        #Obtenemos el id del veterinario
        $veterinarioId = auth()->user()->veterinario?->id;

        #Obtenemos la mascota
        $mascota = Mascota::where('cliente_id', $clienteId)->first();
        #Obtenemos el veterinario
        $veterinario = auth()->user()->veterinario;

        #Obtenemos las proximas citas
        $proximasCitas = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->where('estado', '!=', 'cancelada')->where('fecha_hora', '>=', now())->first();

        #Obtenemos el historial clinico
        $historialClinico = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])
            ->where('estado', '=', 'completada')
            ->where('fecha_hora', '<', now())
            ->orderBy('fecha_hora', 'desc')
            ->first();

        #Obtenemos la proxima cita del veterinario
        $proximaCitaVet = Cita::where('veterinario_id', $veterinarioId)
            ->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])
            ->where('estado', '!=', 'cancelada')
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora', 'asc')
            ->first();

        #Devolvemos la vista con todos los datos
        return Inertia::render('Perfil/Editar', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'proximasCitas' => $proximasCitas,
            'historialClinico' => $historialClinico,
            'proximaCitaVet' => $proximaCitaVet,
            'mascota' => $mascota,
            'veterinario' => $veterinario,
        ]);
    }

    public function actualizar(ProfileUpdateRequest $request): RedirectResponse
    {
        #Actualizamos el perfil
        $request->user()->fill($request->validated());
        #Si el email ha cambiado, actualizamos la fecha de verificacion
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        #Guardamos los cambios

        $request->user()->save();

        return Redirect::route('perfil.editar');
    }

    public function eliminar(Request $request): RedirectResponse
    {

        #Validamos la contraseña
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        #Obtenemos el usuario
        $user = $request->user();

        #Cerramos la sesion
        Auth::logout();
        #Eliminamos el usuario
        $user->delete();
        #Invalidamos la sesion
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function actualizarApi(ProfileUpdateRequest $solicitud)
    {
        #Actualizamos el perfil
        $solicitud->user()->fill($solicitud->validated());
        #Si el email ha cambiado, actualizamos la fecha de verificacion
        if ($solicitud->user()->isDirty('email')) {
            $solicitud->user()->email_verified_at = null;
        }
        #Guardamos los cambios
        $solicitud->user()->save();

        return response()->json(['mensaje' => 'Perfil actualizado']);
    }

    public function actualizarContrasenaApi(Request $solicitud)
    {
        #Validamos la contraseña
        $solicitud->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        #Actualizamos la contraseña

        $solicitud->user()->update([
            'password' => Hash::make($solicitud->password),
        ]);

        return response()->json(['mensaje' => 'Contraseña actualizada']);
    }

    public function eliminarApi(Request $solicitud)
    {
        #Validamos la contraseña
        $solicitud->validate([
            'password' => ['required', 'current_password'],
        ]);
        #Obtenemos el usuario
        $usuario = $solicitud->user();

        #Cerramos la sesion
        Auth::logout();
        #Eliminamos el usuario
        $usuario->delete();
        #Invalidamos la sesion
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return response()->json(['redirect' => '/']);
    }
}
