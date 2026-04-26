<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id', 'tenant_id', 'amount', 'due_date', 'paid_date', 'status', 'reference', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'  => 'date',
            'paid_date' => 'date',
        ];
    }

    public function lease()   { return $this->belongsTo(Lease::class); }
    public function tenant()  { return $this->belongsTo(User::class, 'tenant_id'); }
}
