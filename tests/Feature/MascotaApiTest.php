<?php

use App\Models\Mascota;
use App\Models\User;

test('mascotas api crud for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/mascotas', [
            'nombre' => 'Test',
            'descripcion' => 'Prueba',
            'sexo' => 'macho',
            'esterilizado' => false,
        ])
        ->assertCreated()
        ->assertJsonPath('nombre', 'Test');

    $mascota = Mascota::where('user_id', $user->id)->first();

    $this->actingAs($user)
        ->putJson("/api/mascotas/{$mascota->id}", [
            'nombre' => 'Test Editado',
            'descripcion' => 'Prueba',
            'sexo' => 'macho',
            'esterilizado' => true,
        ])
        ->assertOk()
        ->assertJsonPath('nombre', 'Test Editado');

    $this->actingAs($user)
        ->deleteJson("/api/mascotas/{$mascota->id}")
        ->assertOk();

    expect(Mascota::find($mascota->id))->toBeNull();
});

test('mascotas listado page loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/mascotas')
        ->assertOk();
});
