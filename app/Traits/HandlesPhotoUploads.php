<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesPhotoUploads
{
    /**
     * Procesa y almacena una foto en el disco público.
     */
    protected function procesarFoto(Request $request, string $nombreInput, string $subcarpeta, ?string $urlAnterior = null): ?string
    {
        if (! $request->hasFile($nombreInput)) {
            return $urlAnterior;
        }

        if ($urlAnterior) {
            $this->eliminarFotoFisica($urlAnterior);
        }

        $archivo = $request->file($nombreInput);
        $nombreArchivo = Str::uuid().'.'.$archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs($subcarpeta, $nombreArchivo, 'public');

        return Storage::disk('public')->url($ruta);
    }

    protected function eliminarFotoFisica(?string $urlFoto): void
    {
        if (! $urlFoto) {
            return;
        }

        // Remueve la parte de la URL absoluta si existe
        $rutaRelativa = str_replace(asset('storage/'), '', $urlFoto);

        // También remueve '/storage/' si es una ruta de web relativa
        $rutaRelativa = str_replace('/storage/', '', $rutaRelativa);

        // Remueve cualquier '/' sobrante al inicio
        $rutaRelativa = ltrim($rutaRelativa, '/');

        // Si por alguna razón comienza con 'storage/', lo removemos
        if (str_starts_with($rutaRelativa, 'storage/')) {
            $rutaRelativa = substr($rutaRelativa, 8);
        }

        Storage::disk('public')->delete($rutaRelativa);
    }
}
