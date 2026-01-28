<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'table_concernée',
        'user_id',
        'description',
        'anciennes_valeurs',
        'nouvelles_valeurs'
    ];

    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}