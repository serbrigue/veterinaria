<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| HandlesPhotoUploads Trait
|--------------------------------------------------------------------------
| Provee métodos reutilizables para gestionar la subida y eliminación de fotos
| o archivos dentro de la aplicación. Soporta almacenamiento local ('public')
| y en la nube ('s3'), gestionado automáticamente según la configuración del entorno.
*/

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

    protected function procesarFoto(Request $request, string $nombreInput, string $subcarpeta, ?string $urlAnterior = null): ?string
    {
        // Si no hay archivo, devolvemos la URL anterior
        if (! $request->hasFile($nombreInput)) {
            return $urlAnterior;
        }

        // Si hay URL anterior, la eliminamos
        if ($urlAnterior) {
            $this->eliminarFotoFisica($urlAnterior);
        }

        //Obtenemos el disco de almacenamiento
        $disk = $this->getStorageDisk();
        //Obtenemos el archivo
        $archivo = $request->file($nombreInput);
        //Generamos un nombre unico para el archivo
        $nombreArchivo = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        //Almacenamos el archivo
        $ruta = $archivo->storeAs($subcarpeta, $nombreArchivo, $disk);

        return Storage::disk($disk)->url($ruta);
    }


    protected function eliminarFotoFisica(?string $urlFoto): void
    {
        // Si no hay URL, no hacemos nada
        if (! $urlFoto) {
            return;
        }

        //Obtenemos el disco de almacenamiento
        $disk = $this->getStorageDisk();

        //Si el disco es s3, extraemos la ruta relativa de la URL completa
        if ($disk === 's3') {
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
