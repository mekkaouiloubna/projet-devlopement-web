<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResourceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description'];

    public function resources()
    {
        return $this->hasMany(Resource::class, 'category_id');
    }
}