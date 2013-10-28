<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

//the login route
if (Config::get('hybridauth::routes.login'))
{
	Route::get(Config::get('hybridauth::routes.login'), array(
		'as' => 'hybridauth.routes.login',
		function($provider)
		{
			$app = app();

			Log::debug('HybridAuth: attempting login');

			$profile = $app['frozennode.hybridauth']->attemptAuthentication($provider, $app['hybridauth']);

			Log::debug('HybridAuth: login attempt complete');

			if ($profile)
			{
				Log::debug('HybridAuth: login success');
				Auth::loginUsingId($profile->user->id);
			}
			else
			{
				Log::debug('HybridAuth: login failure');
			}

			return Redirect::back();
		}
	));
}

//the endpoint that the social provider reroutes to
if (Config::get('hybridauth::routes.endpoint'))
{
	Route::get(Config::get('hybridauth::routes.endpoint'), array(
		'as' => 'hybridauth.routes.endpoint',
		function()
		{
			Hybrid_Endpoint::process();
		}
	));
}