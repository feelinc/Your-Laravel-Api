<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest('login');
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify user logged in.
|
*/

Route::filter('userAuthed', function()
{
    $isAuthed   = false;
    $userId     = API::getResource()->getOwnerId();
    $ownerType  = API::getResource()->getOwnerType();

    if ( ! empty($userId) and $ownerType == 'user') {
        try {
            $user = Sentry::findUserById($userId);
            Sentry::login($user, false);
            unset($user);

            $isAuthed = true;
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
        } catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e) {}
    }

    if ( ! $isAuthed) {
        return API::resourceJson(array(
            'message' => 'User logged out or not activated.'
        ), 405);
    }
});

/*
|--------------------------------------------------------------------------
| Permission Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify user permission.
|
*/

Route::filter('permission', function()
{
    $argList = array();

    if (func_num_args() > 0) {
        $argList = func_get_args();

        unset($argList[0]);
        unset($argList[1]);
    }

    if ( ! empty($argList)) {
        // Check permission
        $hasAccess = false;

        try {
            $user = Sentry::getUser();
            
            foreach ($argList as $item) {
                if ($user->hasAccess($item)) {
                    $hasAccess = true;
                } else {
                    $hasAccess = false;
                    break;
                }
            }

            unset($user);
        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {}

        // Return 405 if current user does not having the required permissions
        if ( ! $hasAccess) {
            return API::resourceJson(array(
                'message' => 'Permission denied.'
            ), 405);
        }
    }
});

