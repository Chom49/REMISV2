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
    ];

    public function landlord()             { return $this->belongsTo(User::class, 'landlord_id'); }
    public function leases()               { return $this->hasMany(Lease::class); }
    public function activeLease()          { return $this->hasOne(Lease::class)->where('status', 'active'); }
    public function maintenanceRequests()  { return $this->hasMany(MaintenanceRequest::class); }
}
