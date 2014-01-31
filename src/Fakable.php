<?php
namespace Fakable;

use DB;
use Fakable\Relations\MorphTo;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Generates a fake model
 */
class Fakable
{
	/**
	 * The model to fake
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * The attributes to set on the fake models
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The pool of models
	 *
	 * @var integer
	 */
	protected $pool;

	/**
	 * Whether fake models created should be saved or not
	 *
	 * @var integer
	 */
	protected $saved = true;

	/**
	 * Whether to batch insert models or not
	 *
	 * @var boolean
	 */
	protected $batch = true;

	/**
	 * The relations to seed
	 *
	 * @var array
	 */
	protected $relations = array();

	/**
	 * The generated models
	 *
	 * @var Collection
	 */
	protected $generated = array();

	/**
	 * Create a new Fakable instance
	 *
	 * @param Model   $model
	 * @param array   $attributes
	 * @param boolean $saved
	 */
	public function __construct(Model $model)
	{
		$this->faker = Faker::create();
		$this->model = clone $model;
	}

	/**
	 * Get the Faker instance
	 *
	 * @return Faker
	 */
	public function getFaker()
	{
		return $this->faker;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// OPTIONS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Save or not the generated models
	 *
	 * @param boolean $saved
	 *
	 * @return self
	 */
	public function setSaved($saved)
	{
		$this->saved = $saved;

		return $this;
	}

	/**
	 * batch or not the generated models
	 *
	 * @param boolean $batch
	 *
	 * @return self
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;

		return $this;
	}

	/**
	 * Set the attributes to overwrite on the fake model
	 *
	 * @param array $attributes
	 *
	 * @return self
	 */
	public function setAttributes(array $attributes = array())
	{
		if (!empty($attributes)) {
			$this->attributes = $attributes;
		}

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////////// POOL /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the pool of models
	 *
	 * @param integer $min
	 * @param integer $max
	 *
	 * @return self
	 */
	public function setPool($min, $max = null)
	{
		$max = $max ?: $min + 5;
		$this->pool = $this->faker->randomNumber($min, $max);

		return $this;
	}

	/**
	 * Set the pool from the count of another model
	 *
	 * @param string  $model
	 * @param integer $power
	 *
	 * @return self
	 */
	public function setPoolFromModel($model, $power = 2)
	{
		$this->pool = $model::count() * $power;

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// GENERATION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fake a single model instance
	 *
	 * @param attributes $array
	 *
	 * @return Model
	 */
	public function fakeModel(array $attributes = array(), $generateRelations = true)
	{
		$this->setAttributes($attributes);

		// Get the fakable attributes
		$fakables = $this->model->getFakables();
		$instance = $this->model->newInstance();
		$instance->id = $this->model->count() + sizeof($this->generated) + 1;

		// Generate dummy attributes
		$defaults  = array();
		foreach ($fakables as $attribute => $signature) {
			$signature = (array) $signature;

			if (!method_exists($this->model, $attribute)) {
				$this->callFromSignature($defaults, $attribute, $signature);
			} else {
				$this->relations[] = $this->createRelationSeederFromSignature($attribute, $signature, $defaults, $instance);
			}
		}

		// Fill attributes and save
		$attributes = array_merge($defaults, $this->attributes);
		$instance->fill($attributes);
		if ($instance->usesTimestamps()) {
			$instance->created_at = $attributes['created_at'];
			$instance->updated_at = $attributes['updated_at'];
		}

		// Save instance
		$this->generated[] = $instance;
		if ($this->saved and !$this->batch) {
			$instance->save();
		}

		// Generate relations if necessary
		if ($generateRelations) {
			$this->insertGeneratedRelations();
		}

		return $instance;
	}

	/**
	 * Fake multiple model instances
	 *
	 * @param attributes $array
	 *
	 * @return void
	 */
	public function fakeMultiple(array $attributes = array())
	{
		$this->setAttributes($attributes);

		// Create models
		for ($i = 0; $i <= $this->pool; $i++) {
			$this->fakeModel([], false);
		}

		// Create relations
		$this->insertGeneratedRelations();
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// INSERTION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Insert the generated models as one
	 *
	 * @return void
	 */
	protected function insertGeneratedEntries()
	{
		// Cast all to array
		$entries = Collection::make($this->generated)->map(function ($entry) {
			unset($entry->id);
			return $entry->getAttributes();
		})->all();

		// Insert the entries
		$this->insertEntries($this->model->getTable(), $entries);

		$this->generated = $this->model->get();
	}

	/**
	 * Generate fake relations
	 *
	 * @return void
	 */
	protected function insertGeneratedRelations()
	{
		// Save the created models
		if ($this->batch) {
			$this->insertGeneratedEntries();
		}

		// Build the relations
		$relations = array();
		foreach($this->relations as list($relation, $signature)) {
			$entries = call_user_func_array([$relation, 'generateEntries'], $signature);
			foreach ($entries as $entry) {
				$relations[$relation->getTable()][] = $entry;
			}
		}

		// Generate the relations
		foreach($relations as $table => $entries) {
			$this->insertEntries($table, $entries);
		}
	}

	/**
	 * Insert entries in a table
	 *
	 * @param string $table
	 * @param array  $entries
	 *
	 * @return void
	 */
	protected function insertEntries($table, $entries)
	{
		$slices = array($entries);

		// If the engine is SQLite and we have a lot of seeded entries
		// We'll split the results to not overflow the variable limit
		if (DB::getDriverName() === 'sqlite') {
			$slicer = floor(999 / sizeof($entries[0]));
			$slices = array_chunk($entries, $slicer);
		}

		foreach ($slices as $entries) {
			DB::table($table)->insert($entries);
		}
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// RELATIONSHIPS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a random primary key of a model
	 *
	 * @param string $model
	 * @param array  $notIn
	 *
	 * @return integer
	 */
	public function randomModel($model, array $notIn = array())
	{
		$model  = new $model;
		$models = $model::query();
		if ($notIn) {
			$models = $models->whereNotIn($model->getKeyName(), $notIn);
		}

		return $this->faker->randomElement($models->lists('id'));
	}

	/**
	 * Return an array of random models IDs
	 *
	 * @param string $model
	 *
	 * @return array
	 */
	public function randomModels($model, $min = 5, $max = null)
	{
		// Get a random number of elements
		$max       = $max ?: $min + 5;
		$available = $model::lists('id');
		$available = empty($available) ? range(1, $this->pool) : $available;
		$number    = $this->faker->randomNumber($min, $max);

		$entries = array();
		for ($i = 0; $i <= $number; $i++) {
			$entries[] = $this->faker->randomElement($available);
		}

		return array_unique($entries);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Factor a RelationSeeder from a signature
	 *
	 * @param array  $attribute
	 * @param array  $signature
	 * @param array  $defaults
	 * @param object $instance
	 *
	 * @return array
	 */
	protected function createRelationSeederFromSignature($attribute, $signature, &$defaults, &$instance)
	{
		// Create the RelationSeeder instance
		if (!$type = array_pull($signature, 'relationType')) {
			$relation = $instance->$attribute();
			$type     = class_basename($relation);
		}
		$type     = 'Fakable\Relations\\'.$type;
		$relation = new $type($this, $instance, $attribute);

		// If we passed the foreign key, populate it
		$foreign = array_pull($signature, 'foreignKey') ?: $attribute;
		if ($relation instanceof MorphTo and $foreign) {
			$relation->setForeignKey($foreign);
		}

		// Affect attributes
		$models   = (array) array_pull($signature, 'forModels');
		$defaults = $relation->affectAttributes($defaults, $models);
		if ($relation instanceof MorphTo) {
			$instance->fill($defaults);
		}

		return [$relation, $signature];
	}

	/**
	 * Transform a fakable array to a signature
	 *
	 * @param array  $attributes
	 * @param string $attribute
	 * @param array  $signature
	 *
	 * @return array
	 */
	protected function callFromSignature(array &$attributes, $attribute, $signature)
	{
		// Get the method signature
		if (is_array($signature)) {
			$method    = array_get($signature, 0);
			$arguments = (array) array_get($signature, 1, array());
		} else {
			$method    = $signature;
			$arguments = array();
		}

		// Get the source of the method
		$source = method_exists($this, $method) ? $this : $this->faker;
		$value  = call_user_func_array([$source, $method], $arguments);

		$attributes[$attribute] = $value;

		return $value;
	}
}