<?php

namespace App\Http\Controllers;

use App\Actions\Movie\MovieHandleNote;
use Inertia\Inertia;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Actions\Movie\MovieStore;
use App\Actions\Movie\MovieStoreManual;
use App\Actions\Movie\MovieStoreMulti;
use App\Actions\Movie\MovieUpdate;
use App\Actions\Movie\MovieUpdateSingle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'movies', 'movies_count', 'imdb_ids']]);
    }

    public function index()
    {
        return Inertia::render('Movie/MovieIndex', [
            'auth_user' => Auth::user(),
            'tmdb_api_key' => env('TMDB_API_KEY'),
        ]);
    }

    public function movies(Request $request)
    {
        $getArray = [
            'id',
            'sortable_name',
            'name',
            'paths',
            'filterable',
            'filter_on_quality',
            'quality',
            'watched',
            'movie_db_id',
            'wish_listed',
            'certification',
            'runtime',
            'boutique_id',
        ];

        $formats = json_decode($request->formats);
        $boutiques = json_decode($request->boutiques);

        return Movie::public()
            ->filterFormat($boutiques, 'boutique_id')
            ->filterFormat($formats, 'filter_on_quality')
            ->search($request->term)
            ->wishListed($request->wish_list)
            ->orderBy('sortable_name')
            ->select($getArray)
            ->paginate(50);
    }

    public function movies_count()
    {
        return [
            'total_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->count(),
            '4k_disc_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->where('quality', 2160)->where('medium', 'disc')->count(),
            '4k_digital_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->where('quality', 2160)->where('medium', 'digital')->count(),
            'hd_disc_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->whereIn('quality', [1080, 720, 540])->where('medium', 'disc')->count(),
            'hd_digital_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->whereIn('quality', [1080, 720, 540])->where('medium', 'digital')->count(),
            'sd_disc_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->where('quality', 480)->where('medium', 'disc')->count(),
            'sd_digital_count' => DB::table('movies')->where('publicly_visible', 1)->where('wish_listed', 0)->where('quality', 480)->where('medium', 'digital')->count(),
        ];
    }

    public function imdb_ids(){
        return DB::table('movies')->select('movie_db_id')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            "form.boutique" => ["nullable", "numeric"],
            "form.collection" => ["nullable", "numeric"],
            "form.image" => ["required"],
            "form.medium" => ["required", Rule::in(['digital', 'disc'])],
            "form.name" => ["required", "min:2"],
            "form.quality" => ["required", "numeric", Rule::in([2160, 1080, 720, 540, 480])],
            "form.year" => ["required", "date"],
            "movie_data" => ["required"],
            "movie_full_data" => ["required"]
        ]);

        try {
            (new MovieStore($request))();
            return 'done';
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store_multi(Request $request)
    {
        $request->validate([
            'quality' => ['required', 'numeric', Rule::in([2160, 1080, 720, 540, 480])],
            'medium' => ['required', Rule::in(['digital', 'disc'])],
            'boutique' => 'nullable|numeric',
            'movies' => 'required|array',
            'collection' => ['nullable', 'numeric']
        ]);

        try {
            (new MovieStoreMulti($request))();
            return redirect()->back();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function store_manual(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'poster' => ['required', 'image'],
            'runtime' => ['required', 'numeric', 'min:1'],
            'certification' => ['required', Rule::in(['G', 'PG', 'PG-13', 'R', 'NC-17', 'NR'])],
            'quality' => ['required', Rule::in([2160, 1080, 720, 540, 480])],
            'medium' => ['required', Rule::in(['disc', 'digital'])],
            'tagline' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'manual' => ['required', 'boolean'],
            'release_date' => ['required', 'date'],
            'genres' => ['required']
        ]);

        (new MovieStoreManual($request))();
        return redirect()->back();
    }

    public function store_remove_from_wish_list(Movie $movie)
    {
        $movie->wish_listed = false;
        $movie->date_added = now()->format('Y-m-d');
        $movie->save();
    }

    public function show(Movie $movie)
    {
        return $movie->load(['people', 'boutique', 'collection' => ['movies:id,collection_id,name,sortable_name,release_date,paths']]);
    }

    public function update_single(Request $request, Movie $movie)
    {
        $request->validate([
            "type" => ["required"],
            "value" => ["required"],
        ]);

        return (new MovieUpdateSingle($request, $movie))();
    }

    public function update_note(Request $request, Movie $movie)
    {
        return (new MovieHandleNote($request, $movie))();
    }

    public function update(Request $request, Movie $movie)
    {
        $request->validate([
            "active_backdrop" => ["required"],
            "active_poster" => ["required"],
            "boutique" => ["nullable", "numeric"],
            "collection" => ["nullable", "numeric"],
            "certification" => ["required", "string", Rule::in(["G", "PG", "PG-13", "R", "NC-17", "NR"])],
            "filter_on_quality" => ["required", Rule::in(["4k_disc", "4k_digital", "hd_disc", "hd_digital", "sd_disc", "sd_digital"])],
            "name" => ["required"],
            "release_date" => ["required", "date"],
            "runtime" => ["required", "numeric"],
        ]);

        return (new MovieUpdate($request, $movie))();
    }

    public function destroy(Movie $movie)
    {
        return $movie->delete();
    }
}
