<?php

namespace App\Traits\Movies;

use App\Models\Boutique;

trait MovieTrait
{
    protected function fillableArray($formData, array $movieData)
    {
        return [
            'movie_db_id' => $movieData['id'],
            'imdb_id' => $movieData['imdb_id'],
            'manual' => $formData['manual'],
            'publicly_visible' => $formData['publicly_visible'],
            'wish_listed' => $formData['wish_listed'],
            // 'film_boutique_url' => $formData['film_boutique_url'],
            'date_added' => now()->format('Y-m-d'),
            'notes' => [],
            'name' => array_key_exists('name', $formData)  ? $formData['name'] : $movieData['title'],
            'sortable_name' => $this->getSortableName(array_key_exists('name', $formData) ? $formData['name'] : $movieData['title']),
            'original_poster_id' => $movieData['poster_path'],
            'original_backdrop_id' => $movieData['backdrop_path'],
            'active_poster_id' => array_key_exists('image', $formData) ? $formData['image'] : $movieData['poster_path'],
            'active_backdrop_id' => $movieData['backdrop_path'],
            'paths' => [],
            'release_date' => array_key_exists('year', $formData) ? $formData['year'] : $movieData['release_date'],
            'quality' => $formData['quality'],
            'medium' => $formData['medium'],
            'runtime' => $movieData['runtime'],
            'description' => $movieData['overview'],
            'revenue' => $movieData['revenue'],
            'budget' => $movieData['budget'],
            'tagline' => $movieData['tagline'],
            'certification' => $this->getCertification($movieData['release_dates']),
            'videos' => array_key_exists('videos', $movieData) ? $movieData['videos']['results'] : null,
            'filterable' => ["items" => []],
            'collection_id' => $formData['collection'],
        ];
    }

    private function fillableManualArray($formData)
    {
        return [
            'name' => $formData['name'],
            'sortable_name' => $this->getSortableName( $formData['name']),
            //   poster => ,
            'runtime' => $formData['runtime'],
            'certification' => $formData['certification'],
            'quality' => $formData['quality'],
            'medium' => $formData['medium'],
            'tagline' => $formData['tagline'],
            'description' => $formData['description'],
            'release_date' => $formData['release_date'],
            'filterable' => ["items" => []],
            'manual' => true,
        ];
    }

    private function getSortableName($name)
    {
        $nameArray = explode(' ', $name);
        $first = array_shift($nameArray);

        if (strtolower($first) == 'the') {
            return implode(' ', $nameArray) . ', ' . $first;
        }

        return $name;
    }

    private function associateBoutique($submittedBoutiqueId, $movie)
    {
        if (is_null($submittedBoutiqueId) || empty($submittedBoutiqueId)) return null;

        $boutique = Boutique::find($submittedBoutiqueId);
        $movie->boutique()->associate($boutique);
        $movie->save();

        return $boutique;
    }

    private function addToFilterable($movie, $boutique)
    {
        $filterableArray = [];

        $return_value = match ((int) $movie['quality']) {
            2160 => '4k',
            1080, 720, 540 => 'hd',
            480 => 'sd',
        };

        $filter_quality = "{$return_value}_{$movie['medium']}";

        array_push($filterableArray, strtolower($filter_quality));

        if ($boutique) {
            array_push($filterableArray, $boutique['value']);
        }

        $movie['filterable']['items'] = $filterableArray;
        $movie['filter_on_quality'] = $filter_quality;
        $movie->save();
    }

    private function getCertification($release_dates)
    {
        $usCerts = collect($release_dates['results'])->firstWhere('iso_3166_1', '==', 'US');
        $certificate = $usCerts ? collect($usCerts['release_dates'])
            ->map(function ($item) {
                if (in_array($item['type'], [3, 4])) {
                    return [
                        'cert' => $item['certification'],
                        'order' =>  $item['type'] == 3 ? 0 : 1
                    ];
                }

                return null;
            })
            ->filter(function ($item) {
                return $item;
            })
            ->sortBy('order', SORT_NUMERIC)
            ->first()
            : null;

        return $certificate ? $certificate['cert'] : null;
    }
}
