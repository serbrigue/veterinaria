<?php

namespace App\Traits;

/*
|--------------------------------------------------------------------------
| HasStorageAttributes Trait
|--------------------------------------------------------------------------
| Trait utilizado en los modelos para normalizar las URLs de imágenes y archivos.
| Asegura que las rutas de storage locales se devuelvan como URLs relativas '/storage/...',
| previniendo problemas con cambios de dominio o puerto. También provee accessors predeterminados.
*/

trait HasStorageAttributes
{
    /**
     * Resuelve y normaliza una URL o ruta del storage.
     * Si es una URL externa completa, se mantiene.
     * Si es una ruta local del storage, la convierte a una ruta web relativa '/storage/...'.
     */
    protected function resolveStorageUrl(?string $value): ?string
    {
        //Si no hay valor, devolvemos null
        if (! $value) {
            return null;
        }

        //Si es una URL externa completa (ej: Pinterest, Pravatar, etc.) y no pertenece a storage local
        if (filter_var($value, FILTER_VALIDATE_URL) && ! str_contains($value, '/storage/')) {
            return $value;
        }

        //Si ya contiene '/storage/', extrae la ruta relativa de la web a partir de allí.
        //Esto previene URLs absolutas con hosts o puertos incorrectos (ej: http://localhost vs http://localhost:8000).
        if (str_contains($value, '/storage/')) {
            $pos = strpos($value, '/storage/');

            return substr($value, $pos);
        }

        //Si es una ruta interna relativa (ej: 'especies/fotos/imagen.png')
        $url = asset('storage/' . $value);
        if (str_contains($url, '/storage/')) {
            $pos = strpos($url, '/storage/');

            return substr($url, $pos);
        }

        //Devolvemos la URL relativa
        return '/storage/' . ltrim($value, '/');
    }

    /**
     * Accessor para la propiedad imagen_url.
     */
    public function getImagenUrlAttribute(?string $value): ?string
    {
        return $this->resolveStorageUrl($value);
    }

    /**
     * Accessor para la propiedad foto_perfil_url.  
     */
    public function getFotoPerfilUrlAttribute(?string $value): ?string
    {
        return $this->resolveStorageUrl($value);
    }
}
