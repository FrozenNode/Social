<?php namespace Frozennode\Social;

use Hybrid_Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Frozennode\Social\Social;

class SocialServiceProvider extends ServiceProvider {

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
		$this->package('frozennode/social', 'frozennode/social');

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
		$this->registerSocial();
	}

	private function registerHybridAuth()
	{
		$this->app['hybridauth'] = $this->app->share(function($app) {
			$config = $app['config'];
			$haconfig = $config['frozennode/social::hybridauth'];
			$haconfig['base_url'] = $app['url']->route('social.routes.endpoint');
			$instance = new Hybrid_Auth($haconfig);

			return $instance;
		});
	}

	private function registerSocial()
	{
		$this->app['frozennode.social'] = $this->app->share(function($app)
		{
			$config = array(
				'db' => $app['config']['frozennode/social::db'],
				'hybridauth' => $app['config']['frozennode/social::hybridauth'],
				'models' => $app['config']['frozennode/social::models'],
				'routes' => $app['config']['frozennode/social::routes'],
			);

			$instance = new Social($config);

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
		return array('frozennode.social', 'hybridauth');
	}
}