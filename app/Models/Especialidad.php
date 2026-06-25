<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Especialidad extends Model
{
    use HasFactory, \App\Traits\ClearsCache;

    public $cacheKeys = ['especialidades_full', 'veterinarios_full', 'prestaciones_full'];

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function veterinarios()
    {
        return $this->hasMany(Veterinario::class, 'especialidad_id');
    }
}
