<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsCache
{
    /**
     * El método "boot[NombreDelTrait]" es ejecutado automáticamente por Laravel
     * cuando el modelo arranca.
     */
    protected static function bootClearsCache()
    {
        $clearFunc = function ($model) {
            // Verificamos si el modelo tiene definida la propiedad $cacheKeys
            if (isset($model->cacheKeys) && is_array($model->cacheKeys)) {
                foreach ($model->cacheKeys as $key) {
                    Cache::forget($key);
                }
            }
        };

        // Escuchamos los eventos saved (al crear o actualizar) y deleted (al eliminar)
        static::saved($clearFunc);
        static::deleted($clearFunc);
    }
}
