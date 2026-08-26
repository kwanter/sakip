<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasUuids, HasRoles;

    public $incrementing = false;
    protected $keyType = "string";

    /**
     * The attributes that are mass assignable.
     *
     * SECURITY: email_verified_at is removed from fillable to prevent
     * mass assignment attacks that could bypass email verification.
     * Use dedicated verification methods instead.
     */
    protected $fillable = ["name", "email", "password", "instansi_id"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the instansi that the user belongs to.
     */
    public function instansi()
    {
        return $this->belongsTo(Instansi::class , "instansi_id");
    }

    /**
     * Check if the user is a Super Admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole("Super Admin");
    }

    /**
     * Check if the user has a specific permission.
     * This is a helper method that wraps Spatie's hasPermissionTo method.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }
}