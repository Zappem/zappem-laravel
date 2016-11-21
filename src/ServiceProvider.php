<?php namespace Zappem\ZappemLaravel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

	public function register(){

		$this->mergeConfigFrom(
			__DIR__.'/zappemconfig.php', 
			'services.zappem');

		$this->publishes([
			__DIR__.'/zappemconfig.php' => config_path('zappem.php')
		]);

		$config = $this->app['config']->get('services.zappem');

		if(!$config['zappem_enable']){
			return false;
		}
        
        if (empty($config['project_id'])) {
            throw new \InvalidArgumentException('Zappem project ID not configured');
        }

        if (empty($config['zappem_url'])) {
        	throw new \InvalidArgumentException('Zappem URL not configured');
        }

		$this->app['zappem'] = $this->app->share(function($app) use ($config){
			return new \Zappem\ZappemLaravel\Zappem($config['zappem_url'], $config['project_id']);
		});

		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('Zappem', 'Zappem\ZappemLaravel\ZappemFacade');
		  
	}


}