<?php namespace Frozennode\Social;

use Hybrid_Auth;
use Hybrid_Provider_Adapter;
use Hybrid_User_Profile;
use Illuminate\Log\Writer;
use Illuminate\Support\Facades\Auth;

class Social {

	/**
	 * The package's configuration
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The service used to login
	 *
	 * @var Hybrid_Provider_Adapter $adapter
	 */
	protected $adapter;


	/**
	 * The profile of the current user from the provider, once logged in
	 *
	 * @var Hybrid_User_Profile
	 */
	protected $adapter_profile;

	/**
	 * The name of the current provider, e.g. Facebook, LinkedIn, etc
	 *
	 * @var string
	 */
	protected $provider;

	/**
	 * The logger
	 *
	 * @var Writer
	 */
	protected $logger;

	/**
	 * Create a new Social instance
	 *
	 * @param array		$config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Get a social profile for a user, optionally specifying which social network to get, and which user to query
	 */
	public function getProfile($network = NULL, $user = NULL)
	{
		//if the supplied $user value is null, use the current user
		if ($user === NULL)
		{
			$user = Auth::user();

			//if there is no current user, exit out
			if (!$user)
			{
				return NULL;
			}
		}

		//if the provided network is null, grab the user's first existing social profile
		if ($network === NULL)
		{
			$profile = $user->profiles()->first();
		}
		//otherwise get the specific social profile for this network
		else
		{
			$profile = $user->profiles()->where('network', $network)->first();
		}

		return $profile;
	}

	/**
	 * Gets the enabled social providers from the config
	 *
	 * @return array
	 */
	public function getProviders()
	{
		$haconfig = $this->config['hybridauth'];
		$providers = array();

		//iterate over the social providers in the config
		foreach (array_get($haconfig, 'providers', array()) as $provider => $config)
		{
			if (array_get($config, 'enabled'))
			{
				$providers[] = $provider;
			}
		}

		return $providers;
	}


	/**
	 * Returns the current social provider
	 *
	 * @return string
	 */
	public function getCurrentProvider()
	{
		return $this->provider;
	}

	/**
	 * Sets the current social provider
	 *
	 * @param String	$provider
	 */
	public function setCurrentProvider(String $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * Gets the current provider adapter
	 *
	 * @return \Hybrid_Provider_Adapter
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * Sets the current provider adapter
	 *
	 * @param \Hybrid_Provider_Adapter	$adapter
	 */
	public function setAdapter(Hybrid_Provider_Adapter $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Gets the log writer
	 *
	 * @return \Illuminate\Log\Writer
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * Sets the log writer
	 *
	 * @param \Illuminate\Log\Writer
	 */
	public function setLogger(Writer $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Attempt a login with a given provider
	 *
	 * @param string		$provider
	 * @param Hybrid_Auth	$hybridauth
	 */
	public function attemptAuthentication($provider, Hybrid_Auth $hybridauth)
	{
		$this->provider = $provider;
			$adapter = $hybridauth->authenticate($provider);
			$this->setAdapter($adapter);
			$this->setAdapterProfile($adapter->getUserProfile());
			$profile = $this->findProfile();

			return $profile;
		try
		{

		}
		catch (\Exception $e)
		{
			var_dump($e->getMessage());
		}
	}

	/**
	 * Gets the current adapter profile
	 *
	 * @return \Hybrid_User_Profile
	 */
	public function getAdapterProfile()
	{
		return $this->adapter_profile;
	}

	/**
	 * Sets the current adapter profile
	 *
	 * @param \Hybrid_User_Profile	$profile
	 */
	public function setAdapterProfile(Hybrid_User_Profile $profile)
	{
		$this->adapter_profile = $profile;
	}

	/**
	 * Finds a user's adapter profile
	 *
	 * @return mixed
	 */
	protected function findProfile()
	{
		$adapter_profile = $this->getAdapterProfile();
		$ProfileModel = $this->config['db']['profilemodel'];
		$UserModel = $this->config['db']['usermodel'];
		$user = NULL;

		//check if the provider profile already exists
		$profile_builder = call_user_func_array(
			"$ProfileModel::where",
			array('provider', $this->provider)
		);

		if ($profile = $profile_builder->where('identifier', $adapter_profile->identifier)->first())
		{
			//we found an existing user
			$user = $profile->user()->first();
		}
		elseif ($adapter_profile->email)
		{
			//it's a new profile, but it may not be a new user, so check the users by email
			$user_builder = call_user_func_array(
				"$UserModel::where",
				array('email', $adapter_profile->email)
			);

			$user = $user_builder->first();
		}

		//if we haven't found a user, we need to create a new one
		if (!$user)
		{
			$user = new $UserModel();

			//map in anything from the profile that we want in the User
			$map = $this->config['db']['profiletousermap'];

			foreach ($map as $apkey => $ukey)
			{
				$user->$ukey = $adapter_profile->$apkey;
			}

			$values = $this->config['db']['uservalues'];

			foreach ($values as $key=>$value)
			{
				if (is_callable($value))
				{
					$user->$key = $value($user, $adapter_profile);
				}
				else
				{
					$user->$key = $value;
				}
			}

			//$user->username = $adapter_profile->email;
			$user->email = $adapter_profile->email;

			if (!$user->save($this->config['db']['userrules']))
			{
				return NULL;
			}
		}

		//if we didn't find the profile, we need to create a new one
		if (!$profile)
		{
			$profile = $this->createProfileFromAdapterProfile($adapter_profile, $user);
		}
		//if we did find a profile, make sure we update any changes to the source
		else
		{
			$profile = $this->applyAdapterProfileToExistingProfile($adapter_profile, $profile);
		}

		if (!$profile->save())
		{
			return NULL;
		}

		return $profile;
	}

	/**
	 * Creates a social profile from a HybridAuth adapter profile
	 *
	 * @param \Hybrid_User_Profile	$adapter_profile
	 * @param User					$user
	 *
	 * @return Profile
	 */
	protected function createProfileFromAdapterProfile($adapter_profile, $user)
	{
		$ProfileModel = $this->config['db']['profilemodel'];
		$attributes['provider'] = $this->provider;

		// @todo use config value for foreign key name
		$attributes['user_id'] = $user->id;

		$profile = new $ProfileModel($attributes);
		$profile = $this->applyAdapterProfileToExistingProfile($adapter_profile, $profile);

		return $profile;
	}

	/**
	 * Saves an existing social profile with data from a HybridAuth adapter profile
	 *
	 * @param \Hybrid_User_Profile	$adapter_profile
	 * @param User					$user
	 *
	 * @return Profile
	 */
	protected function applyAdapterProfileToExistingProfile($adapter_profile, $profile)
	{
		$attributes = get_object_vars($adapter_profile);

		foreach ($attributes as $k => $v)
		{
			$profile->$k = $v;
		}

		return $profile;
	}
}