<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'duration',
        'price',
        'active',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
