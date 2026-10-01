<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // role, subrole y active NO son asignables en masa: nadie se sube de rol desde un request.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'active' => 'boolean',
        ];
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return string[] */
    public function permissions(): array
    {
        $cfg = config('permissions');

        return match ($this->role) {
            Role::SuperAdmin => $cfg['all'],
            Role::Instructor => array_values(array_diff($cfg['all'], $cfg['super_admin_only'])),
            Role::Aspirante => $cfg['aspirante'],
            Role::Aprendiz => $cfg['aprendiz'][$this->subrole] ?? [],
            default => [],
        };
    }
}
