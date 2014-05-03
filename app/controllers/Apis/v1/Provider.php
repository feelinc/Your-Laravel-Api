<?php
namespace Apis\v1;

class Provider
{

    /**
     * The Authentication controller class.
     *
     * @var string
     */
    protected $authClass = 'Apis\v1\Auth';

    /**
     * The User controller class.
     *
     * @var string
     */
    protected $userClass = 'Apis\v1\User';

    /**
     * Create a new instance of the Auth.
     *
     * @return Auth
     */
    public function getAuth()
    {
        $class = '\\'.ltrim($this->authClass, '\\');

        return new $class;
    }

    /**
     * Create a new instance of the User.
     *
     * @return User
     */
    public function getUser()
    {
        $class = '\\'.ltrim($this->userClass, '\\');

        return new $class;
    }

}
