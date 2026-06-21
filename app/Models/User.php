<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Available roles in the system.
     */
    public const ROLES = [
        'admin' => 'Admin',
        'kasir' => 'Kasir',
        'audit' => 'Audit',
        'manager' => 'Manager',
        'staff_toko' => 'Staff Toko',
    ];

    /**
     * Check if the user has a role column value matching the given role.
     * Note: This checks the `role` column, NOT Spatie's hasRole().
     * For Spatie role checking, use $user->hasRole() from HasRoles trait.
     */
    public function hasRoleColumn(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has any of the given role column values.
     * Note: This checks the `role` column, NOT Spatie's hasAnyRole().
     * For Spatie role checking, use $user->hasAnyRole() from HasRoles trait.
     */
    public function hasAnyRoleColumn(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if the user is an admin (by role column).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a cashier (by role column).
     */
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    /**
     * Check if the user is an auditor (by role column).
     */
    public function isAudit(): bool
    {
        return $this->role === 'audit';
    }

    /**
     * Check if the user is a manager (by role column).
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if the user is a staff member (by role column).
     */
    public function isStaffToko(): bool
    {
        return $this->role === 'staff_toko';
    }

    /**
     * Get the role display name.
     */
    public function getRoleDisplayName(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}