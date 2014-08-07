<?php
namespace Fakable;

use Illuminate\Console\Command;

/**
 * Implements faking methods to a model
 */
trait FakableModel
{
	/**
	 * The default fakable attributes
	 *
	 * @var array
	 */
	protected $defaultFakables = array(
		'name'       => ['sentence', 5],
		'gender'     => ['numberBetween', [0, 1]],
		'age'        => ['numberBetween', [1, 90]],
		'note'       => ['numberBetween', [1, 10]],
		'contents'   => ['paragraph', 5],
		'biography'  => ['paragraph', 5],
		'email'      => 'email',
		'password'   => 'word',
		'website'    => 'url',
		'address'    => 'address',
		'country'    => 'country',
		'city'       => 'city',
		'private'    => 'boolean',
		'public'     => 'boolean',
		'created_at' => 'dateTimeThisMonth',
		'updated_at' => 'dateTimeThisMonth',
	);

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FAKABLES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fake a new instance
	 *
	 * @param array   $attributes
	 * @param boolean $generateRelations
	 *
	 * @return self
	 */
	public static function fake(array $attributes = array(), $generateRelations = true)
	{
		return static::fakable()->setBatch(false)->fakeModel($attributes, $generateRelations);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// FAKE INSTANCES ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a fakable instance
	 *
	 * @param Command $command
	 *
	 * @return Fakable
	 */
	public static function fakable(Command $command = null)
	{
		$fakable = new Fakable(new static);
		$fakable->setCommand($command);

		return $fakable;
	}

	/**
	 * Fake multiple instances
	 *
	 * @param array   $attributes
	 * @param integer $min
	 * @param integer $max
	 *
	 * @return void
	 */
	public static function fakeMultiple(array $attributes = array(), $min = 5, $max = null)
	{
		return static::fakable()->setBatch(true)->setPool($min, $max)->fakeMultiple($attributes);
	}

	//////////////////////////////////////////////////////////////////////
	///////////////////////// FAKABLE ATTRIBUTES /////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Set the fakable attributes
	 *
	 * @param array $attributes
	 */
	public function setFakableAttributes(array $attributes = array())
	{
		$this->fakables = $attributes;
	}

	/**
	 * Get the fakable attributes
	 *
	 * @return array
	 */
	public function getFakables()
	{
		$defaults   = (array) $this->defaultFakables;
		$attributes = (array) $this->fakables;

		// Don't merge defaults if unguarded
		if (static::$unguarded) {
			return $attributes;
		}

		return array_merge($defaults, $attributes);
	}
}
