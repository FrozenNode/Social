<?php namespace Frozennode\HybridAuth;

class HybridAuth extends \Illuminate\Support\Facades\Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'frozennode.hybridauth'; }
}