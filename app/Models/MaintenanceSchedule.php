<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'date_debut',
        'date_fin',
        'raison',
        'statut'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime'
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}