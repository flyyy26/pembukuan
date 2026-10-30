<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'karyawan_id',
        'name',
        'email',
        'password',
        'can_login',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'can_login'            => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Ambil label role untuk display.
     */
    public function getRoleLabelAttribute(): string
    {
        if ($this->isAdmin()) return 'Admin';
        if ($this->hasRole('karyawan')) return 'Karyawan';
        return '-';
    }
}