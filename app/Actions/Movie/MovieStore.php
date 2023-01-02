<?php

namespace App\Actions\Movie;

use App\Models\Movie;
use App\Events\MovieCreated;
use Illuminate\Http\Request;
use App\Traits\Movies\MovieTrait;

class MovieStore
{
    use MovieTrait;

    public function __construct(public Request $request)
    {
    }

    public function __invoke()
    {
        $movie = $this->createMovie();
        $boutique = $this->associateBoutique($this->request['form']['boutique'], $movie);
        $this->addToFilterable($movie, $boutique);
        MovieCreated::dispatch($movie, $this->request->all());
    }

    private function createMovie()
    {
        return Movie::create(
            $this->fillableArray(
                $this->request['form'],
                $this->request['movie_full_data']
            )
        );
    }
}
