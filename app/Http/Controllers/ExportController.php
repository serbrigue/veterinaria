<?php

namespace App\Http\Controllers;

use App\Exports\BoxesExport;
use App\Exports\CategoriasPrestacionExport;
use App\Exports\CategoriasInsumoExport;
use App\Exports\CitasExport;
use App\Exports\ClientesExport;
use App\Exports\EspecialidadesExport;
use App\Exports\EspeciesExport;
use App\Exports\InsumosExport;
use App\Exports\MascotasExport;
use App\Exports\PrestacionesExport;
use App\Exports\RazasExport;
use App\Exports\SucursalesExport;
use App\Exports\VeterinariosExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    private function resolverExportador(string $entidad): ?array
    {
        // Se crea un arreglo con las clases de los exportadores
        $exportadores = [
            'especies'              => ['clase' => EspeciesExport::class, 'archivo' => 'especies'],
            'razas'                 => ['clase' => RazasExport::class, 'archivo' => 'razas'],
            'especialidades'        => ['clase' => EspecialidadesExport::class, 'archivo' => 'especialidades'],
            'categorias-prestacion' => ['clase' => CategoriasPrestacionExport::class, 'archivo' => 'categorias_prestacion'],
            'categorias-insumo'     => ['clase' => CategoriasInsumoExport::class, 'archivo' => 'categorias_insumo'],
            'sucursales'            => ['clase' => SucursalesExport::class, 'archivo' => 'sucursales'],
            'boxes'                 => ['clase' => BoxesExport::class, 'archivo' => 'boxes'],
            'clientes'              => ['clase' => ClientesExport::class, 'archivo' => 'clientes'],
            'mascotas'              => ['clase' => MascotasExport::class, 'archivo' => 'mascotas'],
            'citas'                 => ['clase' => CitasExport::class, 'archivo' => 'citas'],
            'veterinarios'          => ['clase' => VeterinariosExport::class, 'archivo' => 'veterinarios'],
            'prestaciones'          => ['clase' => PrestacionesExport::class, 'archivo' => 'prestaciones'],
            'insumos'               => ['clase' => InsumosExport::class, 'archivo' => 'insumos'],
        ];

        // Retorna el exportador correspondiente a la entidad
        return $exportadores[$entidad] ?? null;
    }

    public function exportar(string $entidad)
    {
        // Resuelve el exportador correspondiente a la entidad
        $config = $this->resolverExportador($entidad);

        // Si no se encuentra el exportador, retorna un error 404
        if (!$config) {
            abort(404, 'Entidad no encontrada para exportación.');
        }

        // Genera el nombre del archivo con la fecha actual
        $nombreArchivo = $config['archivo'] . '_' . date('Y-m-d') . '.xlsx';

        // Descarga el archivo
        return Excel::download(new $config['clase'](), $nombreArchivo);
    }
}
