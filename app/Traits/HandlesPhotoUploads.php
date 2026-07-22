<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesPhotoUploads
{
    /**
     * Obtiene el disco de almacenamiento configurado.
     * En desarrollo: 'public' (local).
     * En producción: 's3' (AWS S3).
     * Controlado por la variable de entorno FILESYSTEM_DISK.
     */
    protected function getStorageDisk(): string
    {
        return config('filesystems.default');
    }

    /**
     * Procesa y almacena una foto en el disco configurado (public o s3).
     */
    protected function procesarFoto(Request $request, string $nombreInput, string $subcarpeta, ?string $urlAnterior = null): ?string
    {
        if (! $request->hasFile($nombreInput)) {
            return $urlAnterior;
        }

        if ($urlAnterior) {
            $this->eliminarFotoFisica($urlAnterior);
        }

        $disk = $this->getStorageDisk();
        $archivo = $request->file($nombreInput);
        $nombreArchivo = Str::uuid().'.'.$archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs($subcarpeta, $nombreArchivo, $disk);

        return Storage::disk($disk)->url($ruta);
    }

    /**
     * Elimina una foto del disco configurado.
     * Soporta tanto URLs locales (/storage/...) como URLs de S3.
     */
    protected function eliminarFotoFisica(?string $urlFoto): void
    {
        if (! $urlFoto) {
            return;
        }

        $disk = $this->getStorageDisk();

        if ($disk === 's3') {
            // Para S3, extraer la ruta relativa de la URL completa
            $parsedUrl = parse_url($urlFoto);
            $rutaRelativa = ltrim($parsedUrl['path'] ?? '', '/');
        } else {
            // Para disco local: extraer ruta relativa de la URL
            $rutaRelativa = str_replace(asset('storage/'), '', $urlFoto);
            $rutaRelativa = str_replace('/storage/', '', $rutaRelativa);
            $rutaRelativa = ltrim($rutaRelativa, '/');

            if (str_starts_with($rutaRelativa, 'storage/')) {
                $rutaRelativa = substr($rutaRelativa, 8);
            }
        }

        Storage::disk($disk)->delete($rutaRelativa);
    }
}
