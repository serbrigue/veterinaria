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

        $clienteId = auth()->user()->cliente?->id;

        $mascota = Mascota::where('cliente_id', $clienteId)->first();
        $veterinario = Veterinario::all()->first();



        $proximasCitas = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->where('estado', '!=', 'cancelada')->where('fecha_hora', '>=', now())->first();

        $historialClinico = Cita::whereHas('mascota', function ($query) use ($clienteId) {
            $query->where('cliente_id', $clienteId);
        })->with(['mascota.cliente.usuario', 'veterinario.usuario', 'box'])->where('estado', '=', 'completada')->where('fecha_hora', '<', now())->first();


        return Inertia::render('Perfil/Editar', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'proximasCitas' => $proximasCitas,
            'historialClinico' => $historialClinico,
            'mascota' => $mascota,
            'veterinario' => $veterinario,
        ]);
    }

    public function actualizar(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('perfil.editar');
    }

    public function eliminar(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function actualizarApi(ProfileUpdateRequest $solicitud)
    {
        $solicitud->user()->fill($solicitud->validated());

        if ($solicitud->user()->isDirty('email')) {
            $solicitud->user()->email_verified_at = null;
        }

        $solicitud->user()->save();

        return response()->json(['mensaje' => 'Perfil actualizado']);
    }

    public function actualizarContrasenaApi(Request $solicitud)
    {
        $solicitud->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $solicitud->user()->update([
            'password' => Hash::make($solicitud->password),
        ]);

        return response()->json(['mensaje' => 'Contraseña actualizada']);
    }

    public function eliminarApi(Request $solicitud)
    {
        $solicitud->validate([
            'password' => ['required', 'current_password'],
        ]);

        $usuario = $solicitud->user();

        Auth::logout();
        $usuario->delete();
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return response()->json(['redirect' => '/']);
    }
}
