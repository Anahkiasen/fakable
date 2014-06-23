<?php
namespace Fakable;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * Class FakableTestCase
 *
 * @package Fakable
 */
class FakableTestCase extends \PHPUnit_Framework_TestCase
{
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

		self::createTables($capsule);
	}

	protected function setUp()
	{
		DB::table('dummy_fakable_models')->truncate();
	}

	/**
	 *
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
