<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class PanelController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->cliente->id;


        return Inertia::render('App/Panel', [
            'estadisticas' => [
                'mascotas' => Mascota::where('cliente_id', $userId)->count(),
            ],
            'ultimasMascotas' => Mascota::where('cliente_id', $userId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($mascota) => [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'sexo' => $mascota->sexo,
                    'fecha_nacimiento_formato' => $mascota->fecha_nacimiento_formato,
                    'creado_en' => Carbon::parse($mascota->created_at)->locale('es')->diffForHumans(),
                ]),
        ]);
    }
}
