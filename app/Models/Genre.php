<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_db_id',
        'name'
    ];

    //Relationships
    public function movies(){
        return $this->belongsToMany(Movie::class)->withTimestamps();
    }
}
