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
