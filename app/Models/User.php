<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'role', 'password'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isLandlord(): bool { return $this->role === 'landlord'; }
    public function isTenant(): bool   { return $this->role === 'tenant'; }

    public function properties()          { return $this->hasMany(Property::class, 'landlord_id'); }
    public function leasesAsLandlord()    { return $this->hasMany(Lease::class, 'landlord_id'); }
    public function leasesAsTenant()      { return $this->hasMany(Lease::class, 'tenant_id'); }
    public function payments()            { return $this->hasMany(Payment::class, 'tenant_id'); }
    public function maintenanceRequests() { return $this->hasMany(MaintenanceRequest::class, 'tenant_id'); }
}
