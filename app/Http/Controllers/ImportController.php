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
use App\Http\Requests\GuardarImportacionSimpleRequest;
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

    public function analyzeHeaders(AnalyzeImportRequest $request)
    {
        try {
            //Cargamos el archivo excel
            $file = $request->file('file');

            //Extraemos los encabezados y una muestra de datos
            $data = Excel::toArray(new \stdClass, $file);

            //Validamos que el archivo no esté vacío
            if (empty($data) || empty($data[0])) {
                return response()->json(['message' => 'El archivo está vacío.'], 400);
            }

            //Obtenemos los encabezados y una muestra de datos
            $sheet = $data[0];
            $headers = $sheet[0] ?? [];
            $sample = $sheet[1] ?? [];

            // Limpiamos los encabezados
            $headers = array_map('trim', $headers);

            //Retornamos los encabezados y una muestra de datos

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'sample' => $sample
            ]);
        } catch (Exception $e) {

            //Retornamos el mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importData(ProcessImportRequest $request)
    {
        //Decodificamos el mapeo y los módulos
        $mapping = json_decode($request->mapping, true);
        $modules = json_decode($request->modules, true);

        try {
            //Instanciamos el importador consolidado
            $import = new ConsolidatedImport($mapping, $modules);

            //Iniciamos una transacción para asegurar la integridad de los datos
            DB::transaction(function () use ($request, $import) {

                //Importamos los datos
                Excel::import($import, $request->file('file'));
            });

            //Retornamos la respuesta
            $response = [
                'success' => true,
                'message' => 'Importación procesada.',
                'descartados_count' => count($import->descartados),
            ];

            //Si hay datos descartados, creamos un archivo excel con ellos
            if (count($import->descartados) > 0) {

                //Obtenemos los encabezados originales
                $headings = $import->headersOriginales;
                //Añadimos el motivo de descarte
                $headings[] = 'Motivo de Descarte';

                //Generamos un nombre de archivo único
                $fileName = 'importaciones_descartadas_' . time() . '.xlsx';

                //Guardamos el archivo en storage/app/temp_imports/
                Excel::store(new DiscardedImportExport($import->descartados, $headings), 'temp_imports/' . $fileName, 'local');

                //Añadimos la URL de descarga y el mensaje
                $response['download_url'] = route('import.download', ['fileName' => $fileName]);
                $response['message'] = 'Importación parcial completada. Algunas filas fueron descartadas.';
            }

            //Retornamos la respuesta
            return response()->json($response);
        } catch (Exception $e) {

            //Retornamos el mensaje de error
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function downloadDiscarded($fileName)
    {
        //Validamos el nombre para evitar vulnerabilidad de Path Traversal
        if (!preg_match('/^importaciones_descartadas_\d+\.xlsx$/', $fileName)) {
            abort(404);
        }

        //Obtenemos la ruta del archivo
        $path = storage_path('app/temp_imports/' . $fileName);

        if (!file_exists($path)) {
            abort(404, 'El archivo ya no está disponible o ya fue descargado.');
        }

        //Descarga el archivo y automáticamente lo elimina del servidor cuando termine la transferencia.
        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function resolverImportadorSimple(string $entidad): ?string
    {
        //Obtenemos la clase importadora correspondiente a la entidad
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

        //Retornamos la clase importadora o null si no existe
        return $importadores[$entidad] ?? null;
    }

    public function importarSimple(GuardarImportacionSimpleRequest $request, string $entidad)
    {
        //Resolvemos la clase importadora
        $claseImport = $this->resolverImportadorSimple($entidad);

        //Validamos que la entidad sea válida
        if (!$claseImport) {
            return response()->json(['success' => false, 'message' => 'Entidad no válida.'], 404);
        }

        //Iniciamos una transacción para asegurar la integridad de los datos
        try {
            DB::transaction(function () use ($request, $claseImport) {
                //Importamos los datos
                Excel::import(new $claseImport(), $request->file('file'));
            });

            //Retornamos la respuesta
            return response()->json([
                'success' => true,
                'message' => 'Importación de ' . str_replace('-', ' ', $entidad) . ' completada con éxito.',
            ]);
        } catch (Exception $e) {
            //Retornamos el mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
            ], 422);
        }
    }
}
