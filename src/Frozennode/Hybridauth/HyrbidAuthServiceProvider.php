<?php namespace Frozennode\HybridAuth;

use Hybrid_Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class HybridAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('frozennode/hybridauth');

		require_once(__DIR__.'/routes.php');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerHybridAuth();
		$this->registerAnvard();
	}

	private function registerHybridAuth()
	{
		$this->app['hybridauth'] = $this->app->share(function($app) {
			$config = $app['config'];
			$haconfig = $config['hybridauth::hybridauth'];
			$haconfig['base_url'] = $app['url']->route('hybridauth.routes.endpoint');
			$instance = new Hybrid_Auth($haconfig);

			return $instance;
		});
	}

	private function registerAnvard()
	{
		$this->app['frozennode.hybridauth'] = $this->app->share(function($app)
		{
			$config = array(
				'db' => $app['config']['hybridauth::db'],
				'hybridauth' => $app['config']['hybridauth::hybridauth'],
				'models' => $app['config']['hybridauth::models'],
				'routes' => $app['config']['hybridauth::routes'],
			);
			$instance = new HybridAuth($config);
			$instance->setLogger($app['log']);

			return $instance;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('frozennode.hybridauth', 'hybridauth');
	}
}