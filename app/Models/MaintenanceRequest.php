<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'tenant_id', 'title', 'description',
        'priority', 'status', 'due_date', 'viewable_by',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function tenant()   { return $this->belongsTo(User::class, 'tenant_id'); }
}
