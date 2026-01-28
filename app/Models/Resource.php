<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'category_id',
        'responsable_id',
        'description',
        'specifications',
        'statut',
        'est_actif'
    ];

    protected $casts = [
        'specifications' => 'array',
        'est_actif' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function isDisponible()
    {
        return $this->statut === 'disponible' && $this->est_actif;
    }
}