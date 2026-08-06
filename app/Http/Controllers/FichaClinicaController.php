<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\FichaClinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GuardarFichaClinicaRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Insumo;
use App\Models\CitaCargo;
use App\Models\MovimientoInventario;

class FichaClinicaController extends Controller
{
    // Muestra la ficha clínica de una cita
    public function mostrar(Cita $cita)
    {
        // Carga la ficha clínica y las recetas y vacunas
        $ficha = $cita->fichaClinica()->with(['recetas', 'vacunas'])->first();

        // Retorna la ficha clínica
        return response()->json($ficha);
    }

    public function descargarPdf(Cita $cita)
    {
        // Carga la ficha clínica y las recetas y vacunas
        $cita->loadMissing([
            'fichaClinica.recetas',
            'fichaClinica.vacunas',
            'mascota.raza.especie',
            'mascota.cliente.usuario',
            'veterinario.usuario'
        ]);

        // Si no hay ficha clínica, retorna un error 404
        if (!$cita->fichaClinica) {
            return response()->json(['error' => 'No hay ficha clínica registrada para esta cita.'], 404);
        }

        // Genera el PDF de la ficha clínica
        $pdf = Pdf::loadView('pdf.ficha_clinica', [
            'cita' => $cita,
            'ficha' => $cita->fichaClinica
        ]);

        // Descarga el PDF
        return $pdf->download('Ficha_Clinica_' . ($cita->mascota->nombre ?? 'Paciente') . '.pdf');
    }
    public function guardar(GuardarFichaClinicaRequest $request, Cita $cita)
    {
        // Valida los datos de la solicitud
        $request->validated();

        // Inicia una transacción para garantizar la integridad de los datos
        return DB::transaction(function () use ($request, $cita) {

            // Guardar Ficha
            $ficha = FichaClinica::updateOrCreate(
                ['cita_id' => $cita->id],
                [
                    'mascota_id' => $cita->mascota_id,
                    'veterinario_id' => $cita->veterinario_id,
                    'peso_actual' => $request->peso_actual,
                    'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                    'temperatura' => $request->temperatura,
                    'anamnesis' => $request->anamnesis,
                    'sintomas' => $request->sintomas,
                    'diagnostico' => $request->diagnostico,
                ]
            );

            // Guardar Recetas
            $ficha->recetas()->delete();
            if ($request->has('recetas')) {
                foreach ($request->recetas as $receta) {
                    $ficha->recetas()->create([
                        'medicamentos' => $receta['medicamentos'],
                        'indicaciones_generales' => $receta['indicaciones_generales'] ?? null,
                        'comprado_en_clinica' => $receta['comprado_en_clinica'] ?? false,
                    ]);
                    if (!empty($receta['comprado_en_clinica'])) {
                        $insumo = Insumo::where('nombre', $receta['medicamentos'])
                            ->where('sucursal_id', $cita->box->sucursal_id ?? $cita->veterinario->sucursal_id ?? null)
                            ->first();

                        if ($insumo) {
                            $this->agregarCargoSiNoExiste($cita, $insumo);
                        }
                    }
                }
            }

            // Guardar Vacunas
            $ficha->vacunas()->delete();
            if ($request->has('vacunas')) {
                // Itera sobre las vacunas
                foreach ($request->vacunas as $vacuna) {

                    //Registra la vacuna en la ficha clínica

                    $ficha->vacunas()->create([
                        'mascota_id' => $cita->mascota_id,
                        'nombre_vacuna' => $vacuna['nombre_vacuna'],
                        'fecha_aplicacion' => $vacuna['fecha_aplicacion'],
                        'fecha_proxima_dosis' => $vacuna['fecha_proxima_dosis'] ?? null,
                        'numero_lote' => $vacuna['numero_lote'] ?? null,
                        'notas' => $vacuna['notas'] ?? null,
                    ]);

                    // Busca la vacuna en la sucursal para agregarla como cargo
                    $insumo = Insumo::where('nombre', $vacuna['nombre_vacuna'])
                        ->where('sucursal_id', $cita->box->sucursal_id ?? $cita->veterinario->sucursal_id ?? null)
                        ->first();

                    if ($insumo) {
                        $this->agregarCargoSiNoExiste($cita, $insumo);
                    }
                }
            }

            return response()->json($ficha->load('recetas', 'vacunas'));
        });
    }

    private function agregarCargoSiNoExiste(Cita $cita, Insumo $insumo)
    {
        $existe = CitaCargo::where('cita_id', $cita->id)->where('insumo_id', $insumo->id)->exists();
        if (!$existe) {
            // Descontamos el stock (permitiendo que quede en negativo si es necesario)
            $insumo->decrement('stock_actual', 1);

            // Registramos el movimiento para la trazabilidad
            MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo' => 'salida',
                'cantidad' => 1,
                'motivo' => 'Uso clínico en Cita',
                'usuario_id' => auth()->id(),
                'cita_id' => $cita->id,
            ]);

            // Creamos el cargo
            CitaCargo::create([
                'cita_id' => $cita->id,
                'prestacion_id' => $cita->prestacion_id,
                'insumo_id' => $insumo->id,
                'cantidad' => 1,
                'precio_unitario' => $insumo->precio_venta,
                'subtotal' => $insumo->precio_venta * 1,
                'pago_vet' => 0,
            ]);
        }
    }
}
