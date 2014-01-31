<?php
namespace Fakable\Relations;

use Fakable\Abstracts\AbstractRelationSeeder;

class BelongsToMany extends AbstractRelationSeeder
{
	/**
	 * Generate an entry in a table pivot table
	 *
	 * @return array
	 */
	public function generateEntry(array $attributes = array())
	{
		return array_merge(array(
			$this->foreignKey() => $this->model->getKey(),
			$this->otherKey()   => $this->fakable->randomModel($this->getRelated()),
		), $attributes);
	}
}