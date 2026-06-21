<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relación belongsTo con Role.
     * Un usuario pertenece a un Rol.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Comprueba si el usuario tiene un permiso específico asignado a su rol.
     * Si el usuario es administrador supremo, tiene acceso total bypass.
     */
    public function tienePermiso(string $permisoNombre): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->rol) {
            return false;
        }

        // Carga y verifica la existencia del permiso en la tabla pivote a través de la relación del rol
        return $this->rol->permisos()->where('nombre', $permisoNombre)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'admin';
    }

    public function isVeterinario(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'veterinario';
    }

    public function isCliente(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'cliente';
    }

    public function veterinario()
    {
        return $this->hasOne(Veterinario::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }
}
