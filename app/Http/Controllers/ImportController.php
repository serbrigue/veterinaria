<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\ConsolidatedImport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\Http\Requests\AnalyzeImportRequest;
use App\Http\Requests\ProcessImportRequest;
use App\Exports\DiscardedImportExport;
use App\Imports\EspeciesImport;
use App\Imports\RazasImport;
use App\Imports\EspecialidadesImport;
use App\Imports\CategoriasPrestacionImport;
use App\Imports\CategoriasInsumoImport;
use App\Imports\SucursalesImport;
use App\Imports\BoxesImport;
use App\Imports\VeterinariosImport;
use App\Imports\PrestacionesImport;
use App\Imports\InsumosImport;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    /**
     * RF-02: Pre-lectura Estructural.
     * Lee las dos primeras filas del Excel para obtener encabezados y una muestra de datos.
     * RNF-02: Validación de 10 MB.
     */
    public function analyzeHeaders(AnalyzeImportRequest $request)
    {
        try {
            $file = $request->file('file');
            
            // Usamos Excel::toArray para obtener los datos rápidamente.
            // toArray carga todo a memoria, pero al ser max 10MB es manejable. 
            // Para ser más eficientes, podríamos leer solo una muestra, pero toArray 
            // es lo más directo con Laravel Excel para extraer headers.
            $data = Excel::toArray(new \stdClass, $file);

            if (empty($data) || empty($data[0])) {
                return response()->json(['message' => 'El archivo está vacío.'], 400);
            }

            $sheet = $data[0]; // Primera hoja
            $headers = $sheet[0] ?? []; // Fila 1: Encabezados
            $sample = $sheet[1] ?? []; // Fila 2: Muestra (puede estar vacía)

            // Limpiar headers (opcional, trim, etc)
            $headers = array_map('trim', $headers);

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'sample' => $sample
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * RF-01, RNF-01, RNF-03: Procesamiento transaccional definitivo.
     */
    public function importData(ProcessImportRequest $request)
    {
        $mapping = json_decode($request->mapping, true);
        $modules = json_decode($request->modules, true);

        try {
            $import = new ConsolidatedImport($mapping, $modules);
            // RNF-01: Integridad Transaccional
            DB::transaction(function () use ($request, $import) {
                // Ejecutamos la importación. Las excepciones de negocio por fila ahora
                // se capturan internamente en $import->descartados
                Excel::import($import, $request->file('file'));
            });

            $response = [
                'success' => true,
                'message' => 'Importación procesada.',
                'descartados_count' => count($import->descartados),
            ];

            if (count($import->descartados) > 0) {
                $headings = $import->headersOriginales;
                $headings[] = 'Motivo de Descarte';
                
                $fileName = 'importaciones_descartadas_' . time() . '.xlsx';
                // Guardamos en storage/app/temp_imports/
                Excel::store(new DiscardedImportExport($import->descartados, $headings), 'temp_imports/' . $fileName);
                
                $response['download_url'] = route('import.download', ['fileName' => $fileName]);
                $response['message'] = 'Importación parcial completada. Algunas filas fueron descartadas.';
            }

            return response()->json($response);

        } catch (Exception $e) {
            // RNF-03: Feedback UX de Errores. Capturar excepción y retornar el error exacto (por ej. Fila 42).
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422); // 422 Unprocessable Entity es más adecuado para errores de validación de datos
        }
    }

    public function downloadDiscarded($fileName)
    {
        // Validar nombre para evitar vulnerabilidad de Path Traversal
        if (!preg_match('/^importaciones_descartadas_\d+\.xlsx$/', $fileName)) {
            abort(404);
        }

        $path = storage_path('app/temp_imports/' . $fileName);

        if (!file_exists($path)) {
            abort(404, 'El archivo ya no está disponible o ya fue descargado.');
        }

        // Descarga el archivo y automáticamente lo elimina del servidor cuando termine la transferencia.
        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function resolverImportadorSimple(string $entidad): ?string
    {
        $importadores = [
            'especies'              => EspeciesImport::class,
            'razas'                 => RazasImport::class,
            'especialidades'        => EspecialidadesImport::class,
            'categorias-prestacion' => CategoriasPrestacionImport::class,
            'categorias-insumo'     => CategoriasInsumoImport::class,
            'sucursales'            => SucursalesImport::class,
            'boxes'                 => BoxesImport::class,
            'veterinarios'          => VeterinariosImport::class,
            'prestaciones'          => PrestacionesImport::class,
            'insumos'               => InsumosImport::class,
        ];

        return $importadores[$entidad] ?? null;
    }

    public function importarSimple(Request $request, string $entidad)
    {
        $claseImport = $this->resolverImportadorSimple($entidad);

        if (!$claseImport) {
            return response()->json(['success' => false, 'message' => 'Entidad no válida.'], 404);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            DB::transaction(function () use ($request, $claseImport) {
                Excel::import(new $claseImport(), $request->file('file'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Importación de ' . str_replace('-', ' ', $entidad) . ' completada con éxito.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
            ], 422);
        }
    }
}
