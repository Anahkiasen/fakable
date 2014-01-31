<?php
namespace Fakable\Relations;

class MorphTo extends BelongsTo
{
	/**
	 * The foreign key
	 *
	 * @var string
	 */
	protected $foreign;

	/**
	 * Set the foreign key
	 *
	 * @param string $foreign
	 */
	public function setForeignKey($foreign)
	{
		$this->foreign = $foreign;
	}

	/**
	 * Get the foreign key
	 *
	 * @return string
	 */
	public function foreignKey()
	{
		if ($this->foreign) {
			return $this->foreign;
		}

		return parent::foreignKey();
	}

	/**
	 * Affect a model's attributes
	 *
	 * @param array $attributes
	 * @param array $models
	 *
	 * @return array
	 */
	public function affectAttributes(array $attributes, array $models = array())
	{
		$pivot = str_replace('_id', '', $this->foreignKey());
		$model = $this->fakable->getFaker()->randomElement($models);

		$attributes[$pivot.'_type'] = $model;
		$attributes[$pivot.'_id']   = $this->fakable->randomModel($model);

		return $attributes;
	}
}