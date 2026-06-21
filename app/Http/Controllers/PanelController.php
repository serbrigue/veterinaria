<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Evitamos errores en nulo si el usuario logueado no tiene perfil de cliente (ej: admin o veterinario)
        $clienteId = $user->cliente?->id;

        if ($user->isAdmin() || $user->isVeterinario()) {
            // Personal del centro ve estadísticas globales
            $cantidadMascotas = Mascota::count();
            $ultimasMascotas = Mascota::with('cliente.usuario', 'raza.especie')
                ->latest()
                ->limit(5)
                ->get();
        } else {
            // Clientes ven solo sus mascotas
            $cantidadMascotas = $clienteId ? Mascota::where('cliente_id', $clienteId)->count() : 0;
            $ultimasMascotas = $clienteId
                ? Mascota::with('cliente.usuario', 'raza.especie')
                    ->where('cliente_id', $clienteId)
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();
        }

        return Inertia::render('App/Panel', [
            'estadisticas' => [
                'mascotas' => $cantidadMascotas,
            ],
            'ultimasMascotas' => $ultimasMascotas->map(fn ($mascota) => [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'sexo' => $mascota->sexo,
                'fecha_nacimiento_formato' => $mascota->fecha_nacimiento_formato,
                'creado_en' => Carbon::parse($mascota->created_at)->locale('es')->diffForHumans(),
            ]),
        ]);
    }
}
