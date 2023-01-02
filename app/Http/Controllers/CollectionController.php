<?php

namespace App\Http\Controllers;

use App\Actions\Collection\CollectionStore;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index']]);
    }

    public function index(){
        return Collection::all();
    }

    public function store(Request $request){
        $request->validate([
            'name' => ['required'],
            'boutique' => ['nullable', 'numeric']
        ]);

        return (new CollectionStore($request))();
    }
}
