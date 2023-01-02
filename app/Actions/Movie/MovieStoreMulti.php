<?php

namespace App\Actions\Movie;

use App\Models\Movie;
use App\Events\MovieCreated;
use Illuminate\Http\Request;
use App\Traits\Movies\MovieTrait;

class MovieStoreMulti
{
    use MovieTrait;

    public function __construct(public Request $request)
    {
    }

    public function __invoke()
    {
        // dd($this->request->all());
        foreach ($this->request->movies as $m) {
            $movie = Movie::create(
                $this->fillableArray([
                    'quality' => $this->request->quality,
                    'medium' => $this->request->medium,
                    'boutique' => $this->request->boutique,
                    'collection' => $this->request->collection,
                    'manual' => $this->request->manual,
                    'publicly_visible' => $this->request->publicly_visible,
                    'wish_listed' => $this->request->wish_listed,
                ], $m)
            );

            $boutique = $this->associateBoutique($this->request->boutique, $movie);
            $this->addToFilterable($movie, $boutique);
            MovieCreated::dispatch($movie, [
                'movie_full_data' => $m,
                'movie_data' => ['backdrop_id' => $m['backdrop_path']],
                'form' => ['image' => $m['poster_path']]
            ]);
        }
    }
}
