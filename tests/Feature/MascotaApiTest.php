<?php
 
use App\Models\Mascota;
use App\Models\User;

test('mascotas api crud for authenticated user', function () {
    // Inicializamos la base de datos con los roles y usuarios sembrados
    $this->seed();

    // Obtenemos el cliente sembrado por defecto Carlos Pérez y una raza para asociarla
    $user = User::where('email', 'user@prueba.com')->first();
    $cliente = $user->cliente;
    $raza = \App\Models\Raza::first();

    $this->actingAs($user)
        ->postJson('/api/mascotas', [
            'nombre' => 'Test',
            'descripcion' => 'Prueba',
            'sexo' => 'macho',
            'esterilizado' => false,
            'cliente_id' => $cliente->id,
            'raza_id' => $raza->id,
        ])
        ->assertCreated()
        ->assertJsonPath('nombre', 'Test');

    $mascota = Mascota::where('cliente_id', $cliente->id)->where('nombre', 'Test')->first();

    $this->actingAs($user)
        ->putJson("/api/mascotas/{$mascota->id}", [
            'nombre' => 'Test Editado',
            'descripcion' => 'Prueba',
            'sexo' => 'macho',
            'esterilizado' => true,
            'cliente_id' => $cliente->id,
            'raza_id' => $raza->id,
        ])
        ->assertOk()
        ->assertJsonPath('nombre', 'Test Editado');

    $this->actingAs($user)
        ->deleteJson("/api/mascotas/{$mascota->id}")
        ->assertOk();

    expect(Mascota::find($mascota->id))->toBeNull();
});

test('mascotas listado page loads for authenticated user', function () {
    // Inicializamos la base de datos
    $this->seed();

    $user = User::where('email', 'user@prueba.com')->first();

    $this->actingAs($user)
        ->get('/mascotas')
        ->assertOk();
});
