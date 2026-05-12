<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'floor_number', 'unit_number', 'status', 'notes'];

    public function property() { return $this->belongsTo(Property::class); }
    public function leases()   { return $this->hasMany(Lease::class); }
    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active')->latest();
    }
}
