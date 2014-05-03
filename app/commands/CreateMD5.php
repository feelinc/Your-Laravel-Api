<?php

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateMD5 extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'api:createMD5';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a MD5 from string.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        $isUrl = (bool) $this->option('is_url');

        if ($isUrl) {
            $this->info(md5(str_replace('%3D', '=', urlencode($this->argument('string').$this->argument('secret')))));
        } else {
            $this->info(md5($this->argument('string').$this->argument('secret')));
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
                'string', 
                InputArgument::REQUIRED, 
                'String'
            ), 
            array(
                'secret', 
                InputArgument::REQUIRED, 
                'Secret'
            )
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array(
                'is_url', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'Is query string', 
                ''
            )
        );
    }

}