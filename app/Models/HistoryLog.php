<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'user_id',
        'description',
        'anciennes_valeurs',
        'nouvelles_valeurs'
    ];

    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array'
    ];

    public function getAnciennesValeursAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true);
    }

    // Accessor pour nouvelles_valeurs
    public function getNouvellesValeursAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true);
    }

    // Relation avec l'utilisateur (si pas déjà définie)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}