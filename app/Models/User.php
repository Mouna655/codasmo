<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = ['name','email','password','role','is_active'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isOperator(): bool   { return $this->role === 'operator'; }
    public function canEnterData(): bool {
        return in_array($this->role, ['superadmin','operator']) && $this->is_active;
    }

    /**
     * Filament memanggil method ini sebelum memberi akses ke panel.
     * Karyawan tidak akan pernah bisa masuk ke /admin bahkan
     * jika mereka berhasil melewati middleware.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->is_active
            && in_array($this->role, ['superadmin', 'operator']);
    }

    public function getRoleLabelAttribute(): string {
        return match($this->role) {
            'superadmin' => 'Super Admin',
            'operator'   => 'Operator',
            'karyawan'   => 'Karyawan',
            default      => $this->role,
        };
    }
}