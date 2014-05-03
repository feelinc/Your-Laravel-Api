<?php
namespace Apis\v1;

use Illuminate\Support\Facades\Request;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

use Sule\Api\Facades\API;
use Sule\Api\OAuth2\Models\OauthClientEndpoint;
use Sule\Api\OAuth2\Repositories\FluentSession;

use Apis\v1\Templates\User as UserTemplate;

class Auth
{

    /**
     * Handle logging in / logging out a user.
     *
     * @return Response
     */
    public function login()
    {
        $status = 401;

        try
        {
            // Set login credentials
            $credentials = array(
                'email'    => Request::getUser(), 
                'password' => Request::getPassword()
            );

            // Try to authenticate the user
            $response = Sentry::authenticate($credentials, false);
            $status   = 200;
        }
        catch (\Cartalyst\Sentry\Users\LoginRequiredException $e)
        {
            $response = array(
                'message' => 'Provided information is not valid.',
                'errors'  => array(
                    array(
                        'field'   => 'email',
                        'message' => 'Login field is required.'
                    )
                )
            );
        }
        catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e)
        {
            $response = array(
                'message' => 'Provided information is not valid.',
                'errors'  => array(
                    array(
                        'field'   => 'password',
                        'message' => 'Password field is required.'
                    )
                )
            );
        }
        catch (\Cartalyst\Sentry\Users\WrongPasswordException $e)
        {
            $response = array(
                'message' => 'Provided information is not valid.',
                'errors'  => array(
                    array(
                        'field'   => 'password',
                        'message' => 'Wrong password, try again.'
                    )
                )
            );
        }
        catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
        {
            $response = array(
                'message' => 'User was not found.'
            );
        }
        catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e)
        {
            $response = array(
                'message' => 'Your account is not yet activated.'
            );
        }

        // The following is only required if throttle is enabled
        catch (\Cartalyst\Sentry\Throttling\UserSuspendedException $e)
        {
            $response = array(
                'message' => 'Your account is suspended.'
            );
        }
        catch (\Cartalyst\Sentry\Throttling\UserBannedException $e)
        {
            $response = array(
                'message' => 'Your account is banned.'
            );
        }

        // Get current client
        $client = API::getClient();

        // Logging in user
        if ($status == 200) {
            $clientEndpoint = $client->endpoint;
            $clientScopeIds = API::getResource()->getScopeIds();
            $clientScopes   = API::getResource()->getScopes();

            $scopes = array();
            if ( ! empty($clientScopeIds)) {
                foreach ($clientScopeIds as $id) {
                    $scopes[] = array(
                        'id' => $id
                    );
                }
            }

            unset($clientScopeIds);

            if ( ! is_array($clientScopes)) {
                $clientScopes = array();
            }

            // Create a new client endpoint if not exist
            if ( ! is_object($clientEndpoint)) {
                $redirectUri = Request::getSchemeAndHttpHost();

                $clientEndpoint = OauthClientEndpoint::create(array(
                    'client_id'    => $client->id,
                    'redirect_uri' => $redirectUri
                ));
            } else {
                $redirectUri = $clientEndpoint->redirect_uri;
            }

            // Create a new authorization code
            $authCode = API::newAuthorizeRequest('user', $response->id, array(
                'client_id'    => $client->id,
                'redirect_uri' => $redirectUri,
                'scopes'       => $scopes
            ));

            // Authorize the client to a user
            if ( ! empty($authCode)) {
                $params = array(
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $client->id,
                    'client_secret' => $client->secret,
                    'redirect_uri'  => $redirectUri,
                    'code'          => $authCode,
                    'scope'         => implode(',', $clientScopes),
                    'state'         => time()
                );

                $authorizationResponse = API::performAccessTokenFlow(false, $params);

                if (array_key_exists('status', $authorizationResponse)) {
                    $status  = $authorizationResponse['status'];
                    $headers = $authorizationResponse['headers'];

                    unset($authorizationResponse['status']);
                    unset($authorizationResponse['headers']);

                    return API::resourceJson($authorizationResponse, $status, $headers);
                }

                // Merge user data with the new authorization data 
                $authorizationResponse['user'] = new UserTemplate($response);
                $response = $authorizationResponse;

                unset($authorizationResponse);
            } else {
                $response = array(
                    'message' => 'There was a problem while logging you in, please try again or contact customer support.'
                );

                $status = 500;
            }

            unset($scopes);
            unset($clientScopes);

        // Logout user
        } else {

            $user = null;
            try {
                $user = Sentry::getUser();
            } catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {}

            if ( ! is_null($user) and ! is_null($client)) {

                // Cleanup OAuth session
                $session = new FluentSession();
                $session->deleteSession($client->id, 'user', $user->getId());
                unset($session);

                // Logout user via sentry
                Sentry::logout();
            }

            unset($user);
        }

        return API::resourceJson($response, $status);
    }

}
