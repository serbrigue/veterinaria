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

        return $exportadores[$entidad] ?? null;
    }

    public function exportar(string $entidad)
    {
        $config = $this->resolverExportador($entidad);

        if (!$config) {
            abort(404, 'Entidad no encontrada para exportación.');
        }

        $nombreArchivo = $config['archivo'] . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new $config['clase'](), $nombreArchivo);
    }
}
