<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

// Loging in / logging out a user
Route::post('api/v1/authenticate', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.content.md5', 
        'api.oauth'
    ), function() {

        $provider = new \Apis\v1\Provider();

        return $provider->getAuth()->login();

    }
));

// Register a user
Route::post('api/v1/users', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.content.md5', 
        'api.oauth:write', 
        'userAuthed', 
        'permission:user.create'
    ), function() {

        $provider = new \Apis\v1\Provider();

        return $provider->getUser()->create();

    }
));

// Get a user
Route::get('api/v1/users/{id}', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.oauth:read', 
        'userAuthed', 
        'permission:user.view'
    ), function($id) {

        $provider = new \Apis\v1\Provider();
        
        return $provider->getUser()->get($id);

    }
))->where('userId', '[0-9]+');

// Update partially a user
Route::patch('api/v1/users/{id}', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.content.md5', 
        'api.oauth:write', 
        'userAuthed', 
        'permission:user.update'
    ), function($id) {

        $provider = new \Apis\v1\Provider();

        return $provider->getUser()->patch($id);

    }
))->where('userId', '[0-9]+');

// Update a user
Route::put('api/v1/users/{id}', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.content.md5', 
        'api.oauth:write', 
        'userAuthed', 
        'permission:user.update'
    ), function($id) {

        $provider = new \Apis\v1\Provider();

        return $provider->getUser()->update($id);

    }
))->where('userId', '[0-9]+');

// Delete a user
Route::delete('api/v1/users/{id}', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.oauth:write', 
        'userAuthed', 
        'permission:user.delete'
    ), function($id) {

        $provider = new \Apis\v1\Provider();

        return $provider->getUser()->delete($id);

    }
))->where('userId', '[0-9]+');

// Get user collection
Route::get('api/v1/users', array(
    'before' => array(
        'api.ua.required', 
        'api.limit', 
        'api.oauth:read', 
        'userAuthed', 
        'permission:user.view'
    ), function() {

        $provider = new \Apis\v1\Provider();
        
        return $provider->getUser()->all();

    }
));
