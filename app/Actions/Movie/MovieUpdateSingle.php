<?php
namespace App\Actions\Movie;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieUpdateSingle {
    public function __construct(public Request $request, public Movie $movie)
    {
    }

    public function __invoke()
    {
        $this->movie->{$this->request->type} = $this->request->value;

        if($this->request->type == 'filter_on_quality'){
            $this->updateFilterOnQuality();
        }

        $this->movie->save();
        return $this->movie->fresh();
    }

    private function updateFilterOnQuality(){
        $movie = Movie::find($this->movie->id);
        $originalFOQ = $movie->filter_on_quality;
        [$quality_text, $medium] = explode('_', $this->request->value);

        $filterable = collect($movie->filterable)->filter(fn($m) => $m !== $originalFOQ)->toArray();
        array_push($filterable, $this->request->value);

        $quality = match($quality_text) {
            '4k' => 2160,
            'hd' => 1080,
            'sd' => 480
        };

        $this->movie->quality = $quality;
        $this->movie->medium = $medium;
        $this->movie->filterable = $filterable;

    }
}