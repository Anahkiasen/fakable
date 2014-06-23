<?php
namespace Fakable\Dummies;

use Fakable\FakableModel;
use Illuminate\Database\Eloquent\Model;

class DummyFakableModel extends Model
{
	use FakableModel;

	protected $fillable = ['name', 'zipcode'];

	protected $fakables = array(
		'name'    => ['numberBetween', [10, 10]],
		'zipcode' => 'randomLetter',
	);
}
