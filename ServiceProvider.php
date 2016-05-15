<?php namespace Zappem\ZappemLaravel;

//use Illuminate\Routing\Router;
//use Illuminate\Session\SessionManager;
use Zappem;
use \App;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

	protected $defer = false;

	public function register(){

		// if (! getenv('ZAPPEM_TOKEN') and ! $this->app['config']->get('services.zappem')) {
  //           return;
  //       }




		$this->app['zappem'] = $this->app->share(function($app){

			// if (! getenv('ROLLBAR_TOKEN') and ! $this->app['config']->get('services.rollbar')) {
   //          	return;
   //      	}

			$defaults = [
				'access_token' => null
			];

	        $config = array_merge($defaults, $app['config']->get('services.zappem', []));
	        $config['access_token'] = getenv('ZAPPEM_TOKEN') ?: $app['config']->get('services.zappem.access_token');
	        if (empty($config['access_token'])) {
	            throw new InvalidArgumentException('Zappem access token not configured');
	        }

			return new \Zappem\ZappemLaravel\Zappem(123, '5737c0aade404c4705b5d5bd');
		});

		$this->app->booting(function(){
		  $loader = \Illuminate\Foundation\AliasLoader::getInstance();
		  $loader->alias('Zappem', 'Zappem\ZappemLaravel\ZappemFacade');
		});

	}


}