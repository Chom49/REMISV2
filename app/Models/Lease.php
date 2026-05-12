<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'unit_id', 'tenant_id', 'landlord_id',
        'start_date', 'end_date', 'monthly_rent', 'security_deposit',
        'payment_day', 'payment_frequency', 'lease_expiry_reminder_days',
        'lease_terms', 'status',
        'termination_reason', 'termination_notes', 'terminated_at',
        'renewal_of_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function property()   { return $this->belongsTo(Property::class); }
    public function unit()       { return $this->belongsTo(Unit::class); }
    public function tenant()     { return $this->belongsTo(User::class, 'tenant_id'); }
    public function landlord()   { return $this->belongsTo(User::class, 'landlord_id'); }
    public function payments()   { return $this->hasMany(Payment::class); }
    public function renewedFrom(){ return $this->belongsTo(Lease::class, 'renewal_of_id'); }
    public function renewals()   { return $this->hasMany(Lease::class, 'renewal_of_id'); }

    public function isActive(): bool      { return $this->status === 'active'; }
    public function isTerminated(): bool  { return $this->status === 'terminated'; }
    public function isRenewed(): bool     { return $this->status === 'renewed'; }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->end_date, false);
    }
}
