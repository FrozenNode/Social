<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

//the login route
if (Config::get('frozennode/social::routes.login'))
{
	Route::get(Config::get('frozennode/social::routes.login'), array(
		'as' => 'social.routes.login',
		function($provider)
		{
			$app = app();
dd('what');
			$profile = $app['frozennode.social']->attemptAuthentication($provider, $app['social']);

			if ($profile)
			{
				Auth::loginUsingId($profile->user->id);
			}

			return Redirect::back();
		}
	));
}

//the endpoint that the social provider reroutes to
if (Config::get('frozennode/social::routes.endpoint'))
{
	Route::get(Config::get('frozennode/social::routes.endpoint'), array(
		'as' => 'social.routes.endpoint',
		function()
		{
			Hybrid_Endpoint::process();
		}
	));
}