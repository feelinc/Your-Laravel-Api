<?php
namespace Apis\v1;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

use Sule\Api\Facades\API;

use Apis\v1\Templates\User as UserTemplate;

class User
{

    /**
     * Handle creation a user.
     *
     * @return Response
     */
    public function create()
    {
        $statusCode = 200;

        $validationRules = array(
            'email'      => 'required|email',
            'password'   => 'required',
            'group'      => 'required',
            'activated'  => 'required',
            'first_name' => 'required'
        );

        // Do validation
        $validator = Validator::make(Input::all(), $validationRules);

        if ( ! $validator->fails()) {

            try {

                // Let's register a user.
                $user = Sentry::createUser(array(
                    'email'      => Input::get('email'),
                    'password'   => Input::get('password'),
                    'first_name' => Input::get('first_name'),
                    'last_name'  => Input::get('last_name', ''),
                    'activated'  => Input::get('activated')
                ));

                // Find the group using the group id
                $group = Sentry::findGroupByName(Input::get('group'));

                // Assign the group to the user
                $user->addGroup($group);

                // Prepare the user data required
                $response   = new UserTemplate($user);

                // Set status code
                $statusCode = 201;

            } catch (\Cartalyst\Sentry\Users\UserExistsException $e) {
                
                $response = array(
                    'message' => 'Provided information is not valid.',
                    'errors'  => 'User with this login already exists.'
                );

            } catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e) {

                $response = array(
                    'message' => 'Provided information is not valid.',
                    'errors'  => 'Group was not found.'
                );

                // Set status code
                $statusCode = 400;
            }

        } else {

            $response = array(
                'message' => 'Provided information is not valid.',
                'errors'  => $validator->errors()->toArray()
            );

        }

        return API::resourceJson($response, $statusCode);
    }

    /**
     * Handle fetch a user.
     *
     * @param  integer $userId
     * @return Response
     */
    public function get($userId)
    {
        try {

            $user = Sentry::findUserById($userId);

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {

            // Return 404 if user not found
            return API::resourceJson(array(
                'message' => 'Requested user could not be found.'
            ), 404);

        }

        return API::resourceJson(new UserTemplate($user));
    }

    /**
     * Handle update partially a user.
     *
     * @param  integer $userId
     * @return Response
     */
    public function patch($userId)
    {
        try {

            $user = Sentry::findUserById($userId);

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {

            // Return 404 if user not found
            return API::resourceJson(array(
                'message' => 'Requested user could not be found.'
            ), 404);

        }

        $input = Input::all();

        if (isset($input['email'])) {
            $user->setAttribute('email', $input['email']);
        }

        if (isset($input['password'])) {
            $user->setAttribute('password', $input['password']);
        }

        if (isset($input['activated'])) {
            $user->setAttribute('activated', $input['activated']);
        }

        if (isset($input['first_name'])) {
            $user->setAttribute('first_name', $input['first_name']);
        }

        if (isset($input['last_name'])) {
            $user->setAttribute('last_name', $input['last_name']);
        }

        if (isset($input['group'])) {
            try {

                // Find the group using the group id
                $group = Sentry::findGroupByName($input['group']);

                // Assign the group to the user
                $user->addGroup($group);

            } catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e) {

                return API::resourceJson(array(
                    'message' => 'Requested group could not be found.'
                ), 400);

            }
        }

        // Save the user
        $user->save();

        return API::resourceJson(new UserTemplate($user));
    }

    /**
     * Handle update a user.
     *
     * @param  integer $userId
     * @return Response
     */
    public function update($userId)
    {
        try {

            $user = Sentry::findUserById($userId);

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {

            // Return 404 if user not found
            return API::resourceJson(array(
                'message' => 'Requested user could not be found.'
            ), 404);

        }

        $validationRules = array(
            'email'      => 'required|email',
            'first_name' => 'required'
        );

        $input = Input::all();

        // Do validation
        $validator = Validator::make($input, $validationRules);

        if ( ! $validator->fails()) {

            if (isset($input['group'])) {
                try {

                    // Find the group using the group id
                    $group = Sentry::findGroupByName($input['group']);

                    // Assign the group to the user
                    $user->addGroup($group);

                } catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e) {

                    return API::resourceJson(array(
                        'message' => 'Requested group could not be found.'
                    ), 400);

                }
            }

            $user->setAttribute('email', $input['email']);

            if (isset($input['password'])) {
                $user->setAttribute('password', $input['password']);
            }

            if (isset($input['activated'])) {
                $user->setAttribute('activated', $input['activated']);
            }

            if (isset($input['first_name'])) {
                $user->setAttribute('first_name', $input['first_name']);
            }

            if (isset($input['last_name'])) {
                $user->setAttribute('last_name', $input['last_name']);
            }

            // Save the user
            $user->save();

        } else {

            return API::resourceJson(array(
                'message' => 'Provided information is not valid.',
                'errors'  => $validator->errors()->toArray()
            ), 422);

        }

        return API::resourceJson(new UserTemplate($user));
    }

    /**
     * Handle delete a user.
     *
     * @param  integer $userId
     * @return Response
     */
    public function delete($userId)
    {
        try {

            $user = Sentry::findUserById($userId);

        } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {

            // Return 404 if user not found
            return API::resourceJson(array(
                'message' => 'Requested user could not be found.'
            ), 404);

        }

        if ( ! $user->delete()) {

            return API::resourceJson(array(
                'message' => 'Unable to delete the user.'
            ), 500);

        }

        return Response::make('', 204);
    }

    /**
     * Handle fetch user list.
     *
     * @return Response
     */
    public function all()
    {
        $offset = (int) Input::get('offset');
        $limit  = (int) Input::get('limit');

        if ($offset == 1) {
            $offset = 0;
        }
        
        // Query the data
        $userModel = Sentry::getUserProvider()->createModel();

        $query = $userModel->newQuery();

        $total = $query->count();

        if ( ! empty($offset)) {
            $query = $query->skip($offset);
        }

        if ( ! empty($limit)) {
            $query = $query->take($limit);
        }

        $result = $query->get();

        unset($userModel);
        unset($query);
        
        // Collect the result
        $response = array();

        if ($result->count() > 0) {
            foreach ($result as $item) {
                $response[] = new UserTemplate($item);
            }
        }
        
        // Build pagination header
        $pagination = $this->buildPagination($total, $offset, $limit);
        $headers    = array();
        if ( ! empty($pagination)) {
            $headers = array(
                'Link' => $pagination
            );
        }

        unset($pagination);
        unset($result);

        return API::collectionJson($response, 200, $headers);
    }

    /**
     * Build pagination link.
     *
     * @param  integer $total
     * @param  integer $offset
     * @param  integer $limit
     * @return string
     */
    private function buildPagination($total = 0, $offset = 0, $limit = 0)
    {
        $links = array();

        if ($total > 0) {
            $baseUrl = Request::getSchemeAndHttpHost().Request::getPathInfo();

            // Create the first page link
            if ($offset > 0) {
                $param = array();

                $param['offset'] = 0;

                if ( ! empty($limit)) {
                    $param['limit'] = $limit;
                }

                $links[] = '<'.$baseUrl.'?'.http_build_query($param).'>; rel="first"';
            }

            // Create the previous page link
            $prevOffset = $offset - $limit;

            if ($prevOffset > 0) {
                $param = array();

                $param['offset'] = $prevOffset;

                if ( ! empty($limit)) {
                    $param['limit'] = $limit;
                }

                $links[] = '<'.$baseUrl.'?'.http_build_query($param).'>; rel="previous"';
            }

            // Create the next page link
            $nextOffset = $offset + $limit;

            if ($nextOffset < ($total - $limit)) {
                $param = array();

                $param['offset'] = $nextOffset;

                if ( ! empty($limit)) {
                    $param['limit'] = $limit;
                }

                $links[] = '<'.$baseUrl.'?'.http_build_query($param).'>; rel="next"';
            }

            // Create the last page link
            if ($offset < ($total - $limit)) {
                $param = array();

                $param['offset'] = $total - $limit;

                if ( ! empty($limit)) {
                    $param['limit'] = $limit;
                }

                $links[] = '<'.$baseUrl.'?'.http_build_query($param).'>; rel="last"';
            }
        }

        return implode(',', $links);
    }

}
