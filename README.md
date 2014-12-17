# DEPRECATED
## This package is deprecated and will no longer be maintained, I recommend using [Factory Muffin](https://github.com/thephpleague/factory-muffin) instead

-----

# Fakable

Allows the creation and seeding of fake Eloquent models. This is a PHP 5.4+ package.

Can be used in tests or to generate batch of fake entries in the seeds.

## Installation

```
$ composer require anahkiasen/fakable:dev-master
```

## Usage

### Basic usage

Simply add the trait to a model like this :

```php
use Fakable\FakableModel;

class MyModel extends Eloquent
{
  use FakableModel;
}
```

A lot of defaults are built-in to configure what gets assigned to what attribute, but you can override or add to this by modifing the `$fakable` property on your model :

```php
class User extends Eloquent
{
  /**
   * The fakable attributes
   *
   * @var array
   */
  protected $fakables = array(
    'nationality' => 'countryCode',
    'timzeone'    => ['numberBetween', [1, 415]],
  );
}
```

Fakable uses [Faker](https://github.com/fzaninotto/Faker) underneath so you can call any of Faker's methods by either simply passing a method name, or an array of `['method', [argument, argument]]` as seen above.

From then on you can call multiple methods on the model to generate fake instances :

```php
> User::fake()->toArray();
array(
  'id'          => 28,
  'name'        => 'Tempore ipsa aut dolorum sit quod.',
  'slug'        => 'tempore-ipsa-aut-dolorum-sit-quod',
  'age'         => 23,
  'biography'   => 'Culpa debitis dolorem quidem eius. Quis et voluptatibus est. Quia nulla rerum expedita magnam.',
  'email'       => 'nienow.donnie@spinkawisoky.com',
  'website'     => 'http://www.weissnat.com/',
  'address'     => '91274 Schmitt Light Suite 378',
  'country'     => 'Sudan',
  'city'        => 'North Twila',
  'private'     => false,
  'nationality' => 'RU'
)
```

You can also generate multiple models by calling `fakeMultiple(attributes, min, max)` :

```php
User::fakeMultiple(array(
  'name' => 'foobar',
), 5, 10);

User::count() // 7
```

## Advanced usage

To get more control over the flow of your generated fake models you can do this :

```php
User::fakable()
```

This will return a Fakable instance from which you can set various options :

```php
User::fakable()
  ->setPool(10, 20) // Will generate 10 to 20 entries
  ->setPoolFromModel('Discussion', 2) // Will generate 2 users for every discussion
  ->setSaved(false) // Won't save anything to the database
  ->fakeModel() // Generated a single fake model, that's what ::fake() calls
  ->fakeMultiple() // Generate multiple models from the pool set previously
```

### Setting attributes on fake models

You can also set attributes on generated fake models that may not be random :

```php
User::fakable()->fakeMultiple(array(
  'gender' => 'Male',
));
```

## Attributes fixtures

Instead of setting all your attributes on your models you can also create an attributes fixture to hold them all. This file can be anywhere and can be either a PHP, YAML or JSON file.
You tell Fakable where to find it by setting the `Fakable\Fakable::$baseFixture` variable to its path, in the setup of your seeding per example, or the start of your application.

```php
Fakable\Fakable::$baseFixture = app_path().'/tests/fixtures/fakable.yml';
```

You can also set a different file for a specific Fakable instance, like this per example:

```php
User::fakable()->setFixture(__DIR__.'/fixtures/user.json')->fakeModel()
```

The file is a simple array [class => attributes] like this:

```yml
User:
  name: 'name'
  age: ['numberBetween', [0, 20]]
```

## Relationships

Fakable will also seed relationships when possible. Most of the time this is a completely automatic process, you simply add the name of the relationship to the fakable attributes and pass it an empty signature :

```php
protected $fakables = array(
  'discussions' => [],
);
```

Optionally you can pass a number of generated relations to generate by passing a min and/or max :


```php
protected $fakables = array(
  'discussions' => [10, 20],
);
```

Do note, **this currently doesn't work for Has-type relations** : Fakable can only seed from the point of the receiver (ie Belongs-type relations).

The only relation you _really_ need to configure are `morphTo` relations because, well, it's a pain to guess their behavior :

```php
class Image extends Eloquent
{
  protected $fakables = array(
    'illustrable' => [
      'relationType' => 'MorphTo',
      'forModels'    => ['User', 'Discussion']
    ],
  );
}
```

Here, `relationType` will tell Fakable this is a MorphTo relationship, and `forModels` will indicate it what kind of models Image may be a polymorphic relation of.

By default Fakable will use the attribute name as the foreign key (so `illustrable` would look for `illustrable_type` and `illustrable_id`) but you can also pass a `foreignKey` to the signature to specify that :

```php
class Image extends Eloquent
{
  protected $fakables = array(
    'illustrable' => [
      'foreignKey'   => 'imageable',
      'relationType' => 'MorphTo',
      'forModels'    => ['User', 'Discussion']
    ],
  );
}
```
