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
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function veterinario()
    {
        return $this->hasOne(Veterinario::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function isVeterinario(): bool
    {
        return $this->rol === 'veterinario';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }
}
