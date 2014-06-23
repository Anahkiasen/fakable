<?php
namespace Fakable\Relations;

use Fakable\Abstracts\AbstractRelationSeeder;

class MorphToMany extends AbstractRelationSeeder
{
	/**
	 * Generate an entry in a table pivot table
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function generateEntry(array $attributes = array())
	{
		$model = $this->getRelated();

		return array_merge(array(
			$this->foreignKey() => $this->model->getKey(),
			$this->otherKey()   => $this->fakable->randomModel($model),
			$this->getType()    => class_basename($model),
		), $attributes);
	}

	/**
	 * Get the type from the relation
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->getProtectedRelationAttribute('morphType');
	}
}
