<?php namespace Frozennode\Social\Facades;

class Social extends \Illuminate\Support\Facades\Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'frozennode.social'; }
}