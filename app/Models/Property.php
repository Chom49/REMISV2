<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id', 'name', 'address', 'city', 'county', 'total_area',
        'type', 'bedrooms', 'bathrooms', 'rent_amount', 'description', 'status',
        'property_category', 'number_of_units', 'floor_layout', 'image',
    ];

    public function landlord()            { return $this->belongsTo(User::class, 'landlord_id'); }
    public function units()               { return $this->hasMany(Unit::class); }
    public function leases()              { return $this->hasMany(Lease::class); }
    public function activeLease()         { return $this->hasOne(Lease::class)->where('status', 'active'); }
    public function maintenanceRequests() { return $this->hasMany(MaintenanceRequest::class); }

    public function isMultiUnit(): bool
    {
        return $this->property_category === 'multi';
    }

    public function occupiedUnitsCount(): int
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    public function vacantUnitsCount(): int
    {
        return $this->units()->where('status', 'vacant')->count();
    }
}
