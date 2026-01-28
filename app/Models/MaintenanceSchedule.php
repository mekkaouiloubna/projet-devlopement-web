<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    // Champs autorisés pour l'insertion ou la mise à jour
    protected $fillable = [
        'resource_id',
        'date_debut',
        'date_fin',
        'raison',
        'statut',
        'created_by', // ← ID de l'utilisateur qui a créé la maintenance
    ];

    // Conversion automatique des dates
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    // Relation avec la ressource
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    // Relation avec l'utilisateur qui a créé la maintenance
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
