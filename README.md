# iqmop/formatter

This is a library that format your single or multiple object to suit your needs
based on formats config. This is the library that you need before sending your
object ( from database ) send to client or view.

## Installation

```bash
composer require iqomp/formatter
```

## Configuration

You should define one format for each object you save on your database. Please
follow this steps to create new object format config.

This module use [iqomp/config](https://github.com/iqomp/config/) to store all
config related to this module. Create new file named `iqomp/config/formatter.php`
under your main module directory. Fill the file with content as below:

```php
<?php

return [
    'formats' => [
        '/format-name/' => [
            '/field-name/' => [
                'type' => '/field-trans-type/'
            ],
            // ...
        ],
        'my-object' => [
            'id' => [
                'type' => 'number',
            ],
            'name' => [
                'type' => 'text'
            ],
            'created_at' => [
                'type' => 'date',
                'timezone' => 'UTC'
            ]
        ]
    ]
];
```

Update your `composer.json` file so config generator aware of your config:

```json
{
    "extra": {
        "iqomp/config": "iqomp/config/"
    }
}
```

Make sure to call `composer update` for everytime you update the config file.

## Usage

This module create new global class that you can use to format your object, the
class `Iqomp\Formatter\Formatter` is the only class that you'll ever need from
this module.

```php
<?php

use Iqomp\Formatter\Formatter;

// formatting single object
$object = Formatter::format('my-object', $object, $options);

// formatting many object
$objects = Formatter::formatMany('my-object', $objects, $options);
```

## Methods

Class `Iqomp\Formatter\Formatter` define a few method that can be used from everywhere

### static function format(string $format, object $object, array $options=[]): ?object

Format a single object. This method accept arguments:

1. `string $format` The format name as defined on global config.
1. `object $object` The object to format.
1. `array $options` List of additional option to send to each format type handler.

See above usage for example.

### static function formatApply(array $formats, array $objects, array $options=[], string $askey=null): ?array

Apply list of rules to an object without registering it on global config. This
method accept arguments:

1. `array $formats` List of format types to apply to the object
1. `array $objects` List of objects to format.
1. `array $options` List of additional option to send to each format type handler.
1. `string $askey` The object property to use as array key of format result. If
this field is not set, indexed array will be returned.

### static function formatMany(string $format, array $objects, array $options=[], string $askey=null): array

As of `format` but for many objects. See above methods for arguments.

### static function typeApply(string $type, $value, string $field, object $object, $format, $options )

Apply single formatter type to a value. Internally, all above method use this method
to apply format type for every object properties. This method accept parameters:

1. `string $type` Format type to apply to the value.
1. `$value` The value to format.
1. `string $field` The field name from the object this value taken from.
1. `object $object` The original object the value taken from.
1. `mixed $format` The value of field format config.
1. `mixed $options` The options to send to format type handler.

See below example:

```php
<?php

use Iqomp\Formatter\Formattter;

$object = (object)[
    'id' => 1,
    'name' => 'User Name',
    'created' => '2010-01-01 12:22:22'
];

$value = Formatter::applyType('number', $object->id, 'id', $object, [], []);
```

## Custom Handler

In same conditoin, you may find that format types we provide is not enough or is
not match your condition. In this case, you may prefer create your own format type
handler.

There's two type of format type handler this module know. Non collective and
collective action.

### Collective

If your handler is collective type, formatter will collect all object's property
and call the handler at once. This type is good if your handler need to call
external resouces like database, or curl.

The method will be called as below:

```php
$res = Class::method($values, $field, $objects, $format, $options);
```

Returned value of this method is expecting to be array `oldvalue => newvalue` pair.

If the method return `null`, no data will be changed.

This method is called with below arguments:

1. `array $values` Indexed array of all objects property that being formatted or
other objects property based on handler config.
1. `string $field` The field name that being processed.
1. `array $objects` All object that being processed.
1. `array $format` Format config that being implemented to the field.
1. `mixed $options` Format option that send by formatter caller.

Below is an example of collective handler:

```php
    // ...
    public static function addPrefix($values, $field, $objects, $format, $options)
    {
        $result = [];
        $prefix = $options ?? '_';

        foreach ($values as $val) {
            $result[$val] = $prefix . $val;
        }

        return $result;
    }
    // ...
```

### Non Collective

This is the common forma type. All format type handler on this module is using
this type.

The method will be called as below:

```php
$res = Class::method($value, $field, $object, $format, $options)
```

If the method return `null`, no data will be changed.

This method is called with below arguments:

1. `mixed $value` The object property that being formatted.
1. `string $field` The field name that being processed.
1. `object $object` Object that being processed.
1. `array $format` Format config that being implemented to the field.
1. `mixed $options` Format option that send by formatter caller.

Below is an example of non collective handler:

```php
    // ...
    public static function addPrefix($value, $field, $object, $format, $options)
    {
        $prefix = $options ?? '_';

        return $prefix . $val;
    }
    // ...
```

### Creating Custom Handler

Follow this step to create new format type handler:

#### Create Type Handler

Create new class that handle the object property:

```php
<?php

namespace MyModule\Formatter;

class MyHandler
{
    protected static function getPrefix($format, $options): string
    {
        $prefix = '_'; // default

        // get prefix from user formatter config:
        // 'formats' => [
        //      '/name/' => [
        //          '/field/' => [
        //              'type' => 'prefix',
        //              'prefix' => '_'
        //          ]
        //      ]
        //  ]
        if (isset($format['prefix'])) {
            $prefix = $format['prefix'];
        }

        // get prefix from user provided options:
        // $res = Formatter::format('/name/', $/obj/, [
        //     '/field/' => '_'
        // ])
        if ($options) {
            $prefix = '_';
        }

        return $prefix;
    }

    // the config:
    // 'handlers' => [
    //      'prefix-one' => [
    //          'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixSingle',
    //          'collective' => false
    //      ]
    // ]
    public static function addPrefixSingle($value, $field, $object, $format, $options)
    {
        $prefix = self::getPrefix($format, $options);

        return $prefix .  $value;
    }

    // the config
    // 'handlers' => [
    //      'prefix-two' => [
    //          'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixCollective',
    //          'collective' => true,
    //          'field' => null
    //      ]
    // ]
    public static function addPrefixCollective($values, $field, $objects, $format, $options)
    {
        $prefix = self::getPrefix($format, $options);

        $result = [];
        foreach ($values as $value) {
            $result[$value] = $prefix . $value;
        }

        return $result;
    }

    // the config
    // 'handlers' => [
    //      'prefix-three' => [
    //          'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixById',
    //          'collective' => true,
    //          'field' => 'id'
    //      ]
    //  ]
    public static function addPrefixById($values, $field, $objects, $format, $options)
    {
        $prefix = self::getPrefix($format, $options);

        $result = [];
        foreach ($objects as $object) {
            $result[$object->id] = $prefix . $object->$field;
        }

        return $result;
    }

    // the config
    // 'handlers' => [
    //      'prefix-four' => [
    //          'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixByMD5',
    //          'collective' => '_MD5_',
    //          'field' => null
    //      ]
    //  ]
    public static function addPrefixByMD5($values, $field, $objects, $format, $options)
    {
        $prefix = self::getPrefix($format, $options);

        $result = [];
        foreach ($values as $value) {
            $hash = md5($value);
            $result[$hash] = $prefix . $value;
        }

        return $result;
    }
}
```

Returning `null` on type handler will not modify object property.

#### Register Handler Config

This module use [iqomp/config](https://github.com/iqomp/config) for all configs.
Create new file named `iqomp/config/formatter.php` under your modue main folder.

Fill the file with content as below:

```php
return [
    'handlers' => [
        'prefix-one' => [
             'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixSingle',
             'collective' => false
         ],
         'prefix-two' => [
             'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixCollective',
             'collective' => true,
             'field' => null
         ],
         'prefix-three' => [
             'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixById',
             'collective' => true,
             'field' => 'id'
         ],
         'prefix-four' => [
             'handler' => 'MyModule\\Formatter\\MyHandler::addPrefixByMD5',
             'collective' => '_MD5_',
             'field' => null
         ]
    ]
];
```

Then, update your module `composer.json` file to register the new config as below:

```json
    {
        "extra": {
            "iqomp/config": "iqomp/config/"
        }
    }
```

Make sure to call `composer update` after modifying your config file.

## Special Options

This is some special format option that can help you in some condition:

### @rest

Apply current format option to all object properties that don't have format type
options:

```php
return [
    'formats' => [
        '/my-object/' => [
            '@rest' => [
                'type' => 'delete'
            ],
            'id' => [
                'type' => 'number',
            ],
            'created_at' => [
                'type' => 'date'
            ]
        ]
    ]
];
```

Above option will return object with only `id` and `created_at` property. The rest
of object properties will apply `'type' => 'delete'` option.

### @clone

Clone other object property value to current new property, the clone action is done
before format type applied to the property:

```php
return [
    'formats' => [
        '/my-object/' => [
            'user_id' => [
                'type' => 'number'
            ],
            'user' => [
                '@clone' => 'user_id',
                'type' => 'std-id'
            ]
        ]
    ]
];
```

The property `user` will clone the value of unformatted property `user_id`, and
apply format type `std-id` to the `user` property.

### @rename

Rename current object property name. The rename action is done after the object is
formatted.

```php
return [
    'user_id' => [
        '@rename' => 'user',
        'type' => 'std-id'
    ]
];
```

Above option will rename object property `user_id` to `user`.

## Format Types

Below is all known format types defined by this module so far:

### bool/boolean

Convert the value to boolean type

```php
    // ...
    '/field/' => [
        'type' => 'bool' // 'boolean'
    ]
    // ...
```
### clone

Clone other object property value with condition, the different between this option
and special option `@clone` is this type don't format the cloned value and this
type can clone only part of sub property of object property.

It can optionally convert the value with other format type:

```php
    // ...
    '/field/' => [
        'type' => 'clone',
        'source' => [
            'field' => 'user.name.first',
            'type' => 'text' // optional. convert the value to type text
        ]
    ]
    // ...
```

Above option will create new object property named `/field/` with data taken from
`$object->user->name->first`. And conver the value to type `text`. The final value
of property `/field/` is now `Iqomp\Formatter\Object\Text` object.

To create new property with value taken from multiple object property, use option
`sources`:

```php
    // ...
    '/field/' => [
        'type' => 'clone',
        'sources' => [
            'name' => [
                'field' => 'user.name.first',
                'type' => 'text'
            ],
            'bdate' => [
                'field' => 'birthdate',
                'type' => 'date'
            ]
        ]
    ]
    // ...
```

Above option will create new object property named `/field/` with type object. The
object property `name` taken from `$object->user->name->first` and conver the value
to type `text`. The second property is `bdate` that taken from `$object->birthdate`
and convert the value to type `date`.

### custom

Use other handler to modify object property value.

```php
    // ...
    '/field/' => [
        'type' => 'custom',
        'handler' => 'Class::method'
    ]
    // ...
```

The callback will get exactly as format type handler non-collective arguments.

### date

Convert the value to `Iqomp\Formatter\Object\DateTime` object. If timezone not
set, it'll back to php xdefault timezone.

```php
    // ...
    '/field/' => [
        'type' => 'date',
        'timezone' => 'UTC' // optional
    ]
    // ...
```

Please see below for details of this object.

### delete

Remove object property.

```php
    // ...
    '/field/' => [
        'type' => 'delete'
    ]
    // ...
```

### embed

Convert the value to `Iqomp\Formatter\Object\Embed`. It's the one that generate
embed html code of popular video service like youtube, etc.

```php
    // ...
    '/field/' => [
        'type' => 'embed'
    ]
    // ...
```

### interval

Conver the value to `Iqomp\Formatter\Object\Interval`.

```php
    // ...
    '/field/' => [
        'type' => 'interval'
    ]
    // ...
```

Please see below for more information about this object.

### multiple-text

Convert the value to array of `Iqomp\Formatter\Object\Text` with same separator.
It conver the original value `string` to array of object type `text`.

```php
    // ...
    '/field/' => [
        'type' => 'multiple-text',
        'separator' => ',' // 'json'
    ]
    // ...
```

If the value of `separator` is `null`, it will use `PHP_EOL` as the separator.
While if it's `'json'`, it will use `json_decode` to separate the value.

### number

Conver the value to `Iqomp\Formatter\Object\Number`.

```php
    // ...
    '/field/' => [
        'type' => 'number',
        'decimal' => 2 // optional
    ]
    // ...
```

The `final` value will be `int` if decimal is not set, or `float` if the value is
bigger than 0. Please see below for more information about this object.

### text

Conver the value to `Iqomp\Formatter\Object\Text`.

```php
    // ...
    '/field/' => [
        'type' => 'text'
    ]
    // ...
```

Please see below for more information about this object.

### json

Conver the value of json string to array/object with `json_decode`. If property
`format` is there, the value of decoded value will be formatted with the format
supplied.

```php
    // ...
    '/field/' => [
        'type' => 'json',
        'format' => 'my-other-object'
    ]
    // ...
```

### join

Combine text and object property valeu to fill current property. To get the value
of object property, add prefix `$` to the value of array member:

```php
    // ...
    '/field/' => [
        'type' => 'join',
        'fields' => ['My', 'name', 'is', '$user.name.first'],
        'separator' => ' '
    ]
    // ...
```

Above example will create a text `My name is (:name)` where placeholder `(:name)`
value will taken from `$object->user->name->first`.

### rename

Rename object property name.

```php
    // ...
    '/field/' => [
        'type' => 'rename',
        'to' => '/new-field/'
    ]
    // ...
```

### std-id

Convert the value to `Iqomp\Formatter\Object\Std`, which is an object that only
has 1 property that is `id.`

```php
    // ...
    '/field/' => [
        'type' => 'std-id'
    ]
    // ...
```

If you json encode an object property with format type `std-id`, it will be
something like `{"id":val}`.

### switch

A format type that allow you to apply format type to the object property based
on value of some of object property.

```php
    // ...
    '/field/' => [
        'type' => 'switch',
        'case' => [
            '/name-1/' => [
                'field' => '/object-property-name/',
                'operator' => '=',
                'expected' => 1,
                'result' => [
                    'type' => 'number'
                ]
            ],
            '/name-2' => [
                'field' => '/object-property-name/',
                'operator' => '>',
                'expected' => 2,
                'result' => [
                    'type' => 'text'
                ]
            ]
        ]
    ]
    // ...
```

Based on above config, the value of object property `/field/` will apply config
`result` of config above if condition match.

If the value of `object->/object-property-name/` is equal to 1, current property
will be formatted with `'type' => 'number'`. Or if the value is bigger than 2,
it will be formatted with `'type' => 'text'`.

Known operator so far are `=`, `!=`, `>`, `<`, `>=`, `<=`, `in`, and `!in`. For
operator `in` and `!in`, the config `expected` expecting an array.

## Type Object

Some object format type convert the value of object property to an interal object.
The object is implementing `\JsonSerializable` that make it possible to `json_encode`.

Below is list of known object so far:

### Iqomp\Formatter\Object\DateTime

The object that extending `\DateTime` with custom property.

```php
$val->format(string $format);
$val->timezone;
$val->time;
$val->value;
$val->{DateTime functions}(...);
```

### Iqomp\Formatter\Object\Embed

The object that identify and make embed html script of popular video service url.

```php
$val->url;
$val->provider;
$val->html;
```

It will return final URL for `__toString()` and `json_encode`.

### Iqomp\Formatter\Object\Interval

The object that handle interval string.

```php
$val->format(string $format);
$val->interval();
$val->time;
$val->value;
$val->DateTime;
$val->DateInterval;
```

### Iqomp\Formatter\Object\Number

The object that work with number.

```php
$val->value;
$val->format([$decimal=0, [$dec_separator=',', [$tho_separator='.']]]);
```


### Iqomp\Formatter\Object\Std

Simple object that has only one property, which is `id` that taken from original
value of the property.

```php
$val->id;
```

### Iqomp\Formatter\Object\Text

The object that work with text.

```php
$val->chars(int $len);
$val->words(int $len);
$val->safe;
$val->clean;
$val->value;
```

Getting property `safe` and `clean` will return new object `~\Text`. The `safe`
property return `htmlspecialschars($, ENT_QUOTES)` of the original value. The
`clean` property return text with only characters `a-zA-Z0-9 `.
