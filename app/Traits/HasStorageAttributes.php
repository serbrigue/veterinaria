<?php

namespace App\Traits;

trait HasStorageAttributes
{
    /**
     * Resuelve y normaliza una URL o ruta del storage.
     * Si es una URL externa completa, se mantiene.
     * Si es una ruta local del storage, la convierte a una ruta web relativa '/storage/...'.
     */
    protected function resolveStorageUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // Si es una URL externa completa (ej: Pinterest, Pravatar, etc.) y no pertenece a storage local
        if (filter_var($value, FILTER_VALIDATE_URL) && ! str_contains($value, '/storage/')) {
            return $value;
        }

        // Si ya contiene '/storage/', extrae la ruta relativa de la web a partir de allí.
        // Esto previene URLs absolutas con hosts o puertos incorrectos (ej: http://localhost vs http://localhost:8000).
        if (str_contains($value, '/storage/')) {
            $pos = strpos($value, '/storage/');

            return substr($value, $pos);
        }

        // Si es una ruta interna relativa (ej: 'especies/fotos/imagen.png')
        $url = asset('storage/'.$value);
        if (str_contains($url, '/storage/')) {
            $pos = strpos($url, '/storage/');

            return substr($url, $pos);
        }

        return '/storage/'.ltrim($value, '/');
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
