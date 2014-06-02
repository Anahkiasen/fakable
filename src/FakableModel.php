<?php
namespace Fakable;

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
		'gender'     => ['randomNumber', [0, 1]],
		'age'        => ['randomNumber', [1, 90]],
		'note'       => ['randomNumber', [1, 10]],

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
	 * Get the fakable attributes
	 *
	 * @return array
	 */
	public function getFakables()
	{
		return array_merge((array) $this->defaultFakables, (array) $this->fakables);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// FAKE INSTANCES ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a fakable instance
	 *
	 * @param Illuminate\Console\Command $command
	 *
	 * @return Fakable
	 */
	public static function fakable($command = null)
	{
		$fakable = new Fakable(new static);
		$fakable->setCommand($command);

		return $fakable;
	}

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

	/**
	 * Fake multiple instances
	 *
	 * @param array    $attributes
	 * @param integer  $min
	 * @param integer  $max
	 *
	 * @return void
	 */
	public static function fakeMultiple(array $attributes = array(), $min = 5, $max = null)
	{
		return static::fakable()->setBatch(true)->setPool($min, $max)->fakeMultiple($attributes);
	}
}
