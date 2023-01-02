<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'movie_db_id', 'name', 'original_poster_id', 'original_backdrop_id',
        'paths', 'release_date', 'quality',
        'medium', 'description', 'boutique_id',
        'sortable_name', 'filterable', 'filter_on_quality',
        'runtime', 'revenue', 'budget', 'tagline',
        'certification', 'videos', 'movie_genres',
        'rating', 'watched', 'active_poster_id', 'active_backdrop_id',
        'collection_id', 'imdb_id', 'manual',
        'publicly_visible', 'wish_listed', 'film_boutique_url',
        'auteur', 'notes', 'date_added'
    ];

    protected $casts = [
        'paths' => AsCollection::class,
        'filterable' => AsCollection::class,
        'videos' => AsCollection::class,
        'movie_genres' => AsCollection::class,
        'watched' => 'bool',
        'notes' => 'array',
        'wish_listed' => 'bool',
        'publicly_visible' => 'bool',
    ];

    //Scopes
    public function scopeWishListed($query, $status)
    {
        return $query->where('wish_listed', $status);
    }

    public function scopeNotWishListed($query)
    {
        return $query->where('wish_listed', 0);
    }

    public function scopePublic($query)
    {
        if (auth()->user()) {
            return $query;
        } else {
            return $query->where('publicly_visible', 1);
        }
    }

    public function scopeSearch($query, $term = "")
    {
        if (empty($term) || is_null($term)) {
            return $query;
        }
        return $query->where('name', 'LIKE', '%' . $term . '%');
    }

    public function scopeFilterFormat($query, $formats, $field)
    {
        if (is_null($formats) || !count($formats)) {
            return $query;
        }

        return $query->whereIn($field, $formats);
    }

    public function scopeFilterBoutique($query, $boutiques){
        if (is_null($boutiques) || !count($boutiques)) {
            return $query;
        }

        return $query->whereIn('boutique_id', $boutiques);        
    }

    //Relationship
    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function people()
    {
        return $this->belongsToMany(People::class)->withPivot('role', 'character', 'order', 'cast')->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
