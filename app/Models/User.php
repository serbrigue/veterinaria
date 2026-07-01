<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    #Traits para usar HasApiTokens, HasFactory y Notifiable
    #HasApiTokens para usar tokens de API
    #HasFactory para crear datos de prueba
    #Notifiable para usar notificaciones

    use HasApiTokens, HasFactory, Notifiable;

    #Definimos el nombre de la tabla
    protected $table = 'users';

    #Campos que se pueden llenar
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
    ];

    #Campos que no se deben mostrar
    protected $hidden = [
        'password',
        'remember_token',
    ];

    #Casteos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    #Relaciones

    #Un usuario pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    #Un usuario tiene muchos permisos a traves de su rol
    public function tienePermiso(string $permisoNombre): bool
    {
        #Si el usuario es admin, tiene todos los permisos
        if ($this->isAdmin()) {
            return true;
        }

        #Si el usuario no tiene rol, no tiene permisos
        if (!$this->rol) {
            return false;
        }

        #Verifica que el usuario tenga el permiso
        return $this->rol->permisos()->where('nombre', $permisoNombre)->exists();
    }

    #Verifica si el usuario es administrador
    public function isAdmin(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'admin';
    }

    #Verifica si el usuario es veterinario
    public function isVeterinario(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'veterinario';
    }

    #Verifica si el usuario es cliente
    public function isCliente(): bool
    {
        return $this->rol && $this->rol->nombre_interno === 'cliente';
    }

    #Un veterinario tiene un usuario
    public function veterinario()
    {
        return $this->hasOne(Veterinario::class);
    }

    #Un cliente tiene un usuario
    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }
}
