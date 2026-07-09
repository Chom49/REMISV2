<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id', 'tenant_id', 'amount', 'due_date', 'paid_date', 'status', 'reference', 'notes',
        'control_number', 'control_number_generated_at', 'control_number_sent_at', 'control_number_sent_via',
        'nmb_transaction_id', 'nmb_receipt_number', 'nmb_payer_name', 'nmb_payer_mobile', 'nmb_paid_at',
        'fo_verified_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date'                    => 'date',
            'paid_date'                   => 'date',
            'control_number_generated_at' => 'datetime',
            'control_number_sent_at'      => 'datetime',
            'nmb_paid_at'                 => 'datetime',
        ];
    }

    public function lease()   { return $this->belongsTo(Lease::class); }
    public function tenant()  { return $this->belongsTo(User::class, 'tenant_id'); }
}
