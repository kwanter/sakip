<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Permissions via roles
    public function permissions()
    {
        return $this->roles->flatMap->permissions->unique('id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Helper checks
    public function hasRole(string $role): bool
    {
        return $this->roles->contains(fn($r) => $r->name === $role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->contains(fn($r) => in_array($r->name, $roles, true));
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->contains(fn($p) => $p->name === $permission);
    }

    // Override the can method to use our permission system
    public function can($ability, $arguments = [])
    {
        // Check if user has the permission directly or through roles
        if ($this->hasPermission($ability)) {
            return true;
        }

        // Check if user has admin role (admin can do everything)
        if ($this->hasRole('admin')) {
            return true;
        }

        // Fall back to parent can method for other checks
        return parent::can($ability, $arguments);
    }
}
