<?php
namespace Fakable\Abstracts;

use Exception;
use Fakable\Fakable;

/**
 * Base class for seeding a relation
 */
abstract class AbstractRelationSeeder
{
	/**
	 * The Fakable instance
	 *
	 * @var Fakable
	 */
	protected $fakable;

	/**
	 * The relation to seed
	 *
	 * @var Relation
	 */
	protected $relation;

	/**
	 * The model the relation belongs to
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Build a new RelationSeeder
	 *
	 * @param Fakable $fakable
	 */
	public function __construct(Fakable $fakable, $model, $relation)
	{
		$this->fakable  = $fakable;
		$this->model    = clone $model;
		$this->relation = $relation;
	}

	/**
	 * Call a method on the relation
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->relation(), $method], $parameters);
	}

	/**
	 * Get the relation
	 *
	 * @return Relation
	 */
	public function relation()
	{
		if (is_string($this->relation)) {
			$this->relation = $this->model->{$this->relation}();
		}

		return $this->relation;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// RELATION OBJECT ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the kind of relation we're in
	 *
	 * @return string
	 */
	public function getKind()
	{
		return $this->getProtectedRelationAttribute('relation');
	}

	/**
	 * Get a protected relation attribute
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	protected function getProtectedRelationAttribute($attribute)
	{
		$relation = (array) $this->relation();

		return array_get($relation, "\0*\0".$attribute, get_class($this));
	}

	/**
	 * Get the other key
	 *
	 * @return string
	 */
	public function otherKey()
	{
		return explode('.', $this->getOtherKey())[1];
	}

	/**
	 * Get the foreign key
	 *
	 * @return string
	 */
	public function foreignKey()
	{
		return explode('.', $this->getForeignKey())[1];
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE MODEL ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Affect a model's attribute
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function affectAttributes(array $attributes)
	{
		return $attributes;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// PIVOT TABLES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Generate an entry
	 *
	 * @return array
	 */
	public function generateEntry()
	{
		return array();
	}

	/**
	 * Generate multiple entries
	 *
	 * @param integer  $min
	 * @param integer  $max
	 *
	 * @return array
	 */
	public function generateEntries($min = 5, $max = null, array $attributes = array())
	{
		if (empty($this->generateEntry())) {
			return array();
		}

		$entries = [];
		$max  = $max ?: $min + 5;
		$pool = $this->fakable->getFaker()->randomNumber($min, $max);

		for ($i = 0; $i < $pool; $i++) {
			$entries[] = $this->generateEntry($attributes);
		}

		return $entries;
	}
}
