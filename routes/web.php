<?php

use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PeopleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [MovieController::class, 'index'])->name('main');
Route::resource('movies', MovieController::class);
Route::patch('/movies/update/single/{movie}', [MovieController::class, 'update_single'])->name('movies.update_single');
Route::get('movies', [MovieController::class, 'movies'])->name('movies.get');
Route::get('movies/count/all', [MovieController::class, 'movies_count'])->name('movies.count');
Route::get('movies/movie/database/ids', [MovieController::class, 'imdb_ids'])->name('movies.mdb_ids');
Route::post('movies/multi/select', [MovieController::class, 'store_multi'])->name('movies.store_multi');
Route::post('movies/manual/store', [MovieController::class, 'store_manual'])->name('movies.store_manual');
Route::post('movies/remove/from/wishlist/{movie}', [MovieController::class, 'store_remove_from_wish_list'])->name('movies.remove_wishlist');
Route::post('movies/note/update/{movie}', [MovieController::class, 'update_note'])->name('movies.update_note');

Route::resource('boutiques', BoutiqueController::class)->only(['index', 'store']);

Route::get('/person/{person}', [PeopleController::class, 'show'])->name('person');

Route::post('/login', [LoginController::class, 'authenticate'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::resource('collections', CollectionController::class)->only(['index', 'store']);