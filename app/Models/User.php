<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role_id',
        'is_active',
        'type',
        'account_status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function resourcesGerees()
    {
        return $this->hasMany(Resource::class, 'responsable_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function historyLogs()
    {
        return $this->hasMany(HistoryLog::class);
    }

    public function isAdmin()
    {
        return $this->role->nom === 'Admin';
    }

    public function isResponsable()
    {
        return $this->role->nom === 'Responsable';
    }

    public function isAdminOrRespo()
    {
        return in_array($this->role->nom, ['Admin', 'Responsable']);
    }

    public function isAdminOrRespoResource($resource)
    {
        return $this->isAdmin() || ($this->isResponsable() && $this->id === $resource->responsable_id);
    }

    public function isUtilisateur()
    {
        return $this->role->nom === 'Utilisateur';
    }

    public function reportedMessages()
    {
        return $this->hasMany(ReportedMessage::class);
    }

}

