<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_db_id', 'name',
        'profile_pic'
    ];

    //Relationships
    public function movies(){
        return $this->belongsToMany(Movie::class)->withPivot(['role', 'character', 'order'])->withTimestamps();
    }
}
