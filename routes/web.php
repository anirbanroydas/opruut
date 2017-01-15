<?php

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

// Main Index route
Route::get('/', 'IndexController@showPage')->name('index');

// Auth::routes();
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');
Route::post('/api/v1/validate/email/allowed', 'Auth\RegisterController@validateEmailAllowed');

// Search Routes
Route::post('/api/v1/search/stations', 'SearchController@stations')->name('searchStations');

// opruuts Routes
Route::post('/api/v1/find/opruut', 'OpruutController@findOpruut')->name('findOpruut');
Route::post('/api/v1/fetch/opruuts', 'OpruutController@fetchOpruuts')->name('fetchOpruuts');

// Live Stream Routes
Route::post('/api/v1/fetch/livestream', 'LivestreamController@fetchLivestream')->name('fetchLivestream');

// favorites Routes
Route::get('/favorites', 'FavoritesController@showPage')->name('favorites');
Route::post('/api/v1/opruut/favorite/toggle', 'FavoritesController@toggleFavorite')->name('toggleFavorite');
Route::post('/api/v1/fetch/favorites', 'FavoritesController@fetchFavorites')->name('getFavorites');



// TestPage Route
// Route::get('/testpage/search/stations', 'TestingPageController@searchStations')
	//	->name('test_search_stations');
		// ->where(['source_id' => '[0-9]+', 'destination_id' => '[0-9]+']);
		

// Route::get('/testpage/find/opruut/{opruut}', 'TestingPageController@findOpruut')
	//	->name('test_find_opruut');



