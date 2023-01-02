<?php

namespace App\Actions\Movie;

use App\Models\Movie;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use function PHPUnit\Framework\matches;
use Illuminate\Support\Facades\Storage;

class MovieUpdate
{
    public function __construct(public Request $request, public Movie $movie)
    {
    }

    public function __invoke()
    {
        return $this->updateMovie();
    }

    private function updateMovie()
    {
        $this->updateMoviePoster('poster');
        $this->updateMoviePoster('backdrop');

        $this->movie->name = $this->request->name;
        $this->movie->filterable['items'] = $this->updateFilterable($this->movie->filter_on_quality, $this->request->filter_on_quality);
        $this->movie->filter_on_quality = $this->request->filter_on_quality;
        $this->movie->certification = $this->request->certification;
        $this->movie->runtime = $this->request->runtime;
        $this->movie->release_date = $this->request->release_date;
        $this->movie->medium = $this->updateMedium();
        $this->movie->quality = $this->updateMovieQuality();
        $this->movie->boutique_id = $this->request->boutique;
        $this->movie->collection_id = $this->request->collection;
        $this->movie->publicly_visible = $this->request->publicly_visible;

        $this->movie->active_backdrop_id = $this->updateImageId($this->movie->active_backdrop_id, $this->request->active_backdrop['file_path']);
        $this->movie->active_poster_id = $this->updateImageId($this->movie->active_poster_id, $this->request->active_poster['file_path']);;

        $this->movie->save();
        // return $this;
        return $this->movie->fresh();
    }

    private function updateImageId($movieImageId, $requesImageId)
    {
        if ($movieImageId != $requesImageId) {
            return $requesImageId;
        }

        return $movieImageId;
    }

    private function updateFilterable($movieFilterOnQuality, $requestFilterOnQuality)
    {
        $filterable = collect($this->movie->filterable['items'])->filter(function ($value) use ($movieFilterOnQuality) {
            return $value != $movieFilterOnQuality;
        })->toArray();

        // $filterable[] = $requestFilterOnQuality;
        array_push($filterable, $requestFilterOnQuality);

        return array_values($filterable);
    }

    private function updateMedium()
    {
        $exploded = explode('_', $this->request->filter_on_quality);
        return $exploded[1];
    }

    private function updateMovieQuality()
    {
        $exploded = explode('_', $this->request->filter_on_quality);
        return match ($exploded[0]) {
            '4k' => 2160,
            'hd' => 1080,
            'sd' => 480
        };
    }

    private function updateMoviePoster($type)
    {
        if ($this->movie['active_' . $type . '_id'] == $this->request['active_' . $type]['file_path']) return;

        $sizes = match ($type) {
            'poster' => [
                ['url' => 'https://image.tmdb.org/t/p/w400', 'size' =>  'grid', 'location' => 'poster_grid'],
                ['url' => 'https://image.tmdb.org/t/p/w1280', 'size' =>  'large', 'location' => 'poster_large'],
            ],
            'backdrop' => [['url' => 'https://image.tmdb.org/t/p/w1280', 'size' => null, 'location' => 'backdrop']]
        };

        //Download the image
        foreach ($sizes as $size) {
            //First, delete the previous file.
            $file = storage_path("app/public/{$type}s/{$this->movie->paths[$size['location']]}");
            if (is_file($file)) {
                unlink($file);
            }

            $contents = file_get_contents($size['url'] . $this->request['active_' . $type]['file_path']); //sizes: w200, w1280
            $filename = "image_" . Str::uuid() . ".jpg";
            Storage::put("/public/{$type}s/{$filename}", $contents);

            //Associate the image with the movie record
            $this->movie->paths[$size['location']] = $filename;
        }
    }
}
