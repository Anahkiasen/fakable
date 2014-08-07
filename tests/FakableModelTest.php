<?php
namespace Fakable;

use Fakable\TestCases\FakableTestCase;
use Illuminate\Database\Eloquent\Model;

class FakableModelTest extends FakableTestCase
{
	public function testDoesntMergeDefaultsIfUnguarded()
	{
		$attributes = $this->model->getFakables();
		$this->assertArrayHasKey('gender', $attributes);

		Model::unguard();
		$attributes = $this->model->getFakables();
		$this->assertArrayNotHasKey('gender', $attributes);
	}
}
