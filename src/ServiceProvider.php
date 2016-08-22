<?php namespace Zappem\ZappemLaravel;

//use Illuminate\Routing\Router;
//use Illuminate\Session\SessionManager;
use Zappem;
use \App;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

	protected $defer = false;

	public function register(){

		$defaults = [];

		$config = array_merge($defaults, $this->app['config']->get('services.zappem', []));
		$config['zappem_enable'] = getenv('ZAPPEM_ENABLE') ?: $this->app['config']->get('services.zappem.zappem_enable');

		if(!$config['zappem_enable']){
			return false;
		}
		
        $config['project_id'] = getenv('ZAPPEM_PROJECT') ?: $this->app['config']->get('services.zappem.project_id');
        $config['zappem_url'] = getenv('ZAPPEM_URL') ?: $this->app['config']->get('services.zappem.zappem_url');
        
        if (empty($config['project_id'])) {
            throw new \InvalidArgumentException('Zappem project ID not configured');
        }
        if (empty($config['zappem_url'])) {
        	throw new \InvalidArgumentException('Zappem URL not configured');
        }

		$this->app['zappem'] = $this->app->share(function($app) use ($config){
			return new \Zappem\ZappemLaravel\Zappem($config['zappem_url'], $config['project_id']);
		});

		$this->app->booting(function(){
		  $loader = \Illuminate\Foundation\AliasLoader::getInstance();
		  $loader->alias('Zappem', 'Zappem\ZappemLaravel\ZappemFacade');
		});

	}


}