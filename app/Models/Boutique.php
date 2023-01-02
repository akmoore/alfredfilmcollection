<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boutique extends Model
{
    use HasFactory;

    protected $fillable = [
        'display', 'value', 'image'
    ];

    //Relationships
    public function movies(){
        return $this->hasMany(Movie::class);
    }

    public function collections(){
        return $this->hasMany(Collection::class);
    }
}
