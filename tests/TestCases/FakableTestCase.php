<?php
namespace Fakable\TestCases;

use Fakable\Dummies\DummyFakableModel;
use Fakable\Fakable;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * Class FakableTestCase
 *
 * @package Fakable
 */
class FakableTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Fakable
	 */
	protected $fakable;

	/**
	 * @type \Fakable\Dummies\DummyFakableModel
	 */
	protected $model;

	/**
	 * Set up the tests
	 */
	public static function setUpBeforeClass()
	{
		$capsule = new Manager;
		$capsule->addConnection(array(
			'driver'   => 'sqlite',
			'database' => ':memory:'
		));

		// Bind to Eloquent
		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		// Set facades
		DB::setFacadeApplication(new Container);
		DB::swap($capsule->getDatabaseManager());

		// Reguard attributes
		Model::reguard();

		self::createTables($capsule);
	}

	/**
	 * Create dummy instances
	 */
	protected function setUp()
	{
		DB::table('dummy_fakable_models')->truncate();

		$this->model   = new DummyFakableModel();
		$this->fakable = new Fakable($this->model);

		$this->fakable->setSaved(false);
	}

	/**
	 * Mock the database
	 *
	 * @param $capsule
	 */
	protected static function createTables($capsule)
	{
		$schema = $capsule->schema();

		$schema->dropIfExists('dummy_fakable_models');
		$schema->create('dummy_fakable_models', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('zipcode');
			$table->timestamps();
		});
	}
}
