<?php

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputArgument;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

class CreateAdministrator extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'api:createAdministrator';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a user administrator.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        $email      = $this->argument('email');
        $password   = $this->argument('password', 'admin123');
        $firstName  = $this->argument('first_name', 'Admin');
        $lastName   = $this->argument('last_name', 'Tea');

        if (empty($email)) {
            $email = 'admin@mine.com';
        }

        if (empty($password)) {
            $password = 'admin123';
        }

        if (empty($firstName)) {
            $firstName = 'Admin';
        }

        if (empty($lastName)) {
            $lastName = 'Tea';
        }

        try {

            // Let's register a user.
            $user = Sentry::createUser(array(
                'email'      => $email,
                'password'   => $password,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'activated'  => true
            ));

            // Find the group using the group id
            $group = Sentry::findGroupByName('Administrators');

            // Assign the group to the user
            $user->addGroup($group);

        } catch (\Cartalyst\Sentry\Users\UserExistsException $e) {
            
            $this->error('User with this login already exists');

        } catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e) {

            $this->error('Group was not found');

        }
	}

	/**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array(
                'email', 
                InputArgument::OPTIONAL, 
                'Email'
            ), 
            array(
                'password', 
                InputArgument::OPTIONAL, 
                'Password'
            ), 
            array(
                'first_name', 
                InputArgument::OPTIONAL, 
                'First Name'
            ), 
            array(
                'last_name', 
                InputArgument::OPTIONAL, 
                'Last Name'
            )
        );
    }

}