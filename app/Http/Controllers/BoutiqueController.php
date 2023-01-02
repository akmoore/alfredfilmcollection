<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use Illuminate\Http\Request;
use App\Actions\Boutique\BoutiqueStore;
use Illuminate\Database\Eloquent\Builder;

class BoutiqueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index']]);
    }

    public function index(Request $request){
        return Boutique::withCount(['movies' => function(Builder $query) use ($request){
            $query->where('wish_listed', $request->wish_list);
        }])->get();
    }

    public function store(Request $request){
        $request->validate([
            "boutique" => ["required", "min:3"],
            "image" => ["nullable", "image"]
        ]);

        return (new BoutiqueStore($request))();
    }
}
