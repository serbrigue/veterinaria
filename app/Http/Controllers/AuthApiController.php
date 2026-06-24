<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Rol;
use App\Models\Cliente;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function registrarse(Request $solicitud)
    {
        $solicitud->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $rolCliente = Rol::where('nombre_interno', 'cliente')->first();

        $usuario = User::create([
            'name' => $solicitud->name,
            'email' => $solicitud->email,
            'password' => Hash::make($solicitud->password),
            'rol_id' => $rolCliente?->id,
        ]);

        Cliente::create([
            'user_id' => $usuario->id,
        ]);

        event(new Registered($usuario));

        Auth::login($usuario);

        return response()->json(['redirect' => route('perfil.editar')], 201);
    }

    public function iniciarSesion(LoginRequest $solicitud)
    {
        $solicitud->authenticate();
        $solicitud->session()->regenerate();

        return response()->json(['redirect' => route('perfil.editar')]);
    }

    public function cerrarSesion(Request $solicitud)
    {
        Auth::guard('web')->logout();

        $solicitud->session()->invalidate();

        $solicitud->session()->regenerateToken();

        return response()->json(['redirect' => '/']);
    }

    public function recuperarContrasena(Request $solicitud)
    {
        $solicitud->validate(['email' => 'required|email']);

        $estado = Password::sendResetLink($solicitud->only('email'));

        if ($estado == Password::RESET_LINK_SENT) {
            return response()->json(['mensaje' => __($estado)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($estado)],
        ]);
    }

    public function restablecerContrasena(Request $solicitud)
    {
        $solicitud->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $estado = Password::reset(
            $solicitud->only('email', 'password', 'password_confirmation', 'token'),
            function ($usuario) use ($solicitud) {
                $usuario->forceFill([
                    'password' => Hash::make($solicitud->password),
                ])->save();
            }
        );

        if ($estado == Password::PASSWORD_RESET) {
            return response()->json(['redirect' => route('iniciar-sesion')]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($estado)],
        ]);
    }

    public function confirmarContrasena(Request $solicitud)
    {
        if (! Auth::guard('web')->validate([
            'email' => $solicitud->user()->email,
            'password' => $solicitud->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $solicitud->session()->put('auth.password_confirmed_at', time());

        return response()->json(['redirect' => route('perfil.editar')]);
    }

    public function verificacionEnviar(Request $solicitud)
    {
        if ($solicitud->user()->hasVerifiedEmail()) {
            return response()->json(['redirect' => route('perfil.editar')]);
        }

        $solicitud->user()->sendEmailVerificationNotification();

        return response()->json(['mensaje' => 'verification-link-sent']);
    }
}
