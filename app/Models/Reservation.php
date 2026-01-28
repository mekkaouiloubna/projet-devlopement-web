<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource_id',
        'date_debut',
        'date_fin',
        'justification',
        'statut',
        'commentaire_responsable',
        'approuve_par',
        'approuve_le'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'approuve_le' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function estActive()
    {
        return $this->statut === 'active';
    }
}