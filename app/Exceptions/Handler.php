<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];


    // Codigo para capturar errores y mostrar paginas personalizadas 

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Manejo de errores HTTP para redireccionamiento a paginas personalizadas
        $this->renderable(function (HttpException $e, $request) {

            // Si hay un error 403 o 404,
            if (in_array($e->getStatusCode(), [403, 404])) {
                // Y la peticion es JSON,
                if ($request->expectsJson() || $request->wantsJson()) {
                    // Devolvemos un JSON con el mensaje de error
                    $msg = $e->getStatusCode() === 403 ? 'No autorizado.' : 'No encontrado.';
                    return response()->json(['error' => $msg], $e->getStatusCode());
                }

                // Mostramos la pagina de error 404 o 403 con Inertia
                $msg = $e->getStatusCode() === 403
                    ? 'No tienes permisos para acceder a esta sección.'
                    : 'La página que buscas no existe o ha sido movida.';

                return Inertia::render('Errores/Error', [
                    'status' => $e->getStatusCode(),
                    'mensaje' => $msg,
                ])->toResponse($request)->setStatusCode($e->getStatusCode());
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['error' => 'No autorizado.'], 403);
            }

            return Inertia::render('Errores/Error', [
                'status' => 403,
                'mensaje' => 'No tienes permisos para acceder a esta sección.',
            ])->toResponse($request)->setStatusCode(403);
        });
    }
}
