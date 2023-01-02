<?php

namespace App\Http\Controllers;

use App\Models\People;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function show(People $person){
        return $person->load('movies:paths,id,sortable_name,release_date');
    }
}
