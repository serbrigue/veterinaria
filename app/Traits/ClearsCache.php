<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| ClearsCache Trait
|--------------------------------------------------------------------------
| Este trait se encarga de limpiar automáticamente la caché relacionada a un
| modelo cuando este es creado, actualizado o eliminado.
| Requiere que el modelo defina una propiedad `$cacheKeys` que contenga un
| array con los nombres de las claves de caché a limpiar.
*/

trait ClearsCache
{
    /**
     * El método "boot[NombreDelTrait]" es ejecutado automáticamente por Laravel
     * cuando el modelo arranca.
     */
    protected static function bootClearsCache()
    {
        // Definimos la función que se ejecutará cuando el modelo sea guardado o eliminado

        $clearFunc = function ($model) {

            // Verificamos si el modelo tiene definida la propiedad $cacheKeys
            if (isset($model->cacheKeys) && is_array($model->cacheKeys)) {

                // Recorremos la propiedad $cacheKeys
                foreach ($model->cacheKeys as $key) {

                    // Eliminamos la caché
                    Cache::forget($key);
                }
            }
        };

        // Escuchamos los eventos saved (al crear o actualizar) y deleted (al eliminar)
        static::saved($clearFunc);
        static::deleted($clearFunc);
    }
}
