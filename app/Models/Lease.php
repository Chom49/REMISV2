<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'tenant_id', 'landlord_id',
        'start_date', 'end_date', 'monthly_rent', 'security_deposit',
        'payment_day', 'payment_frequency', 'lease_expiry_reminder_days', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function property()  { return $this->belongsTo(Property::class); }
    public function tenant()    { return $this->belongsTo(User::class, 'tenant_id'); }
    public function landlord()  { return $this->belongsTo(User::class, 'landlord_id'); }
    public function payments()  { return $this->hasMany(Payment::class); }
}
