<?php
namespace Fakable\Relations;

use Fakable\Abstracts\AbstractRelationSeeder;

class BelongsTo extends AbstractRelationSeeder
{
	/**
	 * Affect a model's attributes
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function affectAttributes(array $attributes)
	{
		$attributes[$this->getForeignKey()] = $this->fakable->randomModel($this->getRelated());

		return $attributes;
	}
}
