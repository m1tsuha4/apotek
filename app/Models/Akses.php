<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akses extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'akses_user', 'akses_id', 'user_id');
    }
}
