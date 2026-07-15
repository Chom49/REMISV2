<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'role', 'password', 'landlord_id', 'tenant_status',
                           'tin', 'nida_number', 'gender', 'nationality',
                           'force_password_change', 'default_password_hint', 'invitation_status',
                           'profile_picture', 'preferences'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'preferences'       => 'array',
        ];
    }

    public function preference(string $key, mixed $default = null): mixed
    {
        return ($this->preferences ?? [])[$key] ?? $default;
    }

    public function isLandlord(): bool         { return $this->role === 'landlord'; }
    public function isTenant(): bool           { return $this->role === 'tenant'; }
    public function isAdmin(): bool            { return $this->role === 'admin'; }
    public function isFinancialOfficer(): bool { return $this->role === 'financial_officer'; }

    public function financialOfficers()
    {
        return $this->hasMany(User::class, 'landlord_id')->where('role', 'financial_officer');
    }

    public function hasActiveFinancialOfficer(): bool
    {
        return $this->financialOfficers()->where('tenant_status', 'active')->exists();
    }

    public function shouldRecommendFinancialOfficer(): bool
    {
        if ($this->hasActiveFinancialOfficer() || $this->preference('fo_recommendation_dismissed', false)) {
            return false;
        }

        return $this->createdTenants()
            ->where('role', 'tenant')
            ->where('tenant_status', 'active')
            ->count() > 3;
    }

    public function properties()          { return $this->hasMany(Property::class, 'landlord_id'); }
    public function createdByLandlord()   { return $this->belongsTo(User::class, 'landlord_id'); }
    public function createdTenants()      { return $this->hasMany(User::class, 'landlord_id'); }
    public function leasesAsLandlord()    { return $this->hasMany(Lease::class, 'landlord_id'); }
    public function leasesAsTenant()      { return $this->hasMany(Lease::class, 'tenant_id'); }
    public function payments()            { return $this->hasMany(Payment::class, 'tenant_id'); }
    public function maintenanceRequests() { return $this->hasMany(MaintenanceRequest::class, 'tenant_id'); }
}
