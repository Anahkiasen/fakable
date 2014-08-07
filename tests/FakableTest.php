<?php
namespace Fakable;

use Fakable\Dummies\DummyFakableModel;
use Fakable\TestCases\FakableTestCase;
use Mockery;

class FakableTest extends FakableTestCase
{
	public function testCanSetPoolFromOtherModel()
	{
		Mockery::mock('alias:SomeModel', ['count' => 5]);

		$this->fakable->setPoolFromModel('SomeModel', 2);

		$this->assertEquals(10, $this->fakable->getPool());
	}

	public function testCanGetFakerInstance()
	{
		$this->assertInstanceOf('Faker\Generator', $this->fakable->getFaker());
	}

	public function testCanUseFakerToGeneratePool()
	{
		$this->fakable->setPool(5, 10);

		$this->assertGreaterThanOrEqual(5, $this->fakable->getPool());
		$this->assertLessThanOrEqual(10, $this->fakable->getPool());
	}

	public function testCanSetPool()
	{
		$this->fakable->setPool(10, 10);

		$this->assertEquals(10, $this->fakable->getPool());
	}

	public function testCanFakeSingleModel()
	{
		$model = $this->fakable->fakeModel();

		$this->assertEquals(10, $model->name);
	}

	public function testCanBindCommand()
	{
		$command = Mockery::mock('Illuminate\Console\Command');
		$command->shouldReceive('comment')->once()->with('Insert entries');
		$this->fakable->setCommand($command);

		$this->fakable->fakeModel();
	}

	public function testCanCallGeneratorWithoutArguments()
	{
		$model = $this->fakable->fakeModel();

		$this->assertRegExp('/[a-z]/', $model->zipcode);
	}

	public function testCanSetCustomAttributes()
	{
		$this->fakable->setAttributes(['name' => 'bar']);
		$model = $this->fakable->fakeModel();

		$this->assertEquals('bar', $model->name);
	}

	public function testCanGetRandomGeneratedModel()
	{
		$this->fakable->fakeModel();
		$this->fakable->fakeModel();

		$model = $this->fakable->randomModel('Fakable\Dummies\DummyFakableModel', [1]);

		$this->assertEquals(2, $model);
	}

	public function testCanUseFixtureAsSource()
	{
		$this->fakable->setFixture(__DIR__.'/_meta/fixture.php');

		$model = $this->fakable->fakeModel();

		$this->assertRegExp('/[a-z]/', $model->name);
	}

	public function testCanUseYamlFixtureAsSource()
	{
		$this->fakable->setFixture(__DIR__.'/_meta/fixture.yml');

		$model = $this->fakable->fakeModel();

		$this->assertRegExp('/[a-z]/', $model->name);
		$this->assertRegExp('/[0-9]{1,2}/', (string) $model->zipcode);
	}

	public function testCanUseJsonFixtureAsSource()
	{
		$this->fakable->setFixture(__DIR__.'/_meta/fixture.json');

		$model = $this->fakable->fakeModel();

		$this->assertRegExp('/[a-z]/', $model->name);
	}

	public function testCanSetFixtureForAllFutureFakableInstances()
	{
		Fakable::$baseFixture = __DIR__.'/_meta/fixture.yml';
		$model = new DummyFakableModel();
		$model = $model->fakable()->fakeModel();

		$this->assertRegExp('/[a-z]/', $model->name);
	}
}
