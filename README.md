# Silverstripe link
Adds a Linkfield for [gorriecoe/silverstripe-link](https://github.com/gorriecoe/silverstripe-link)

## Installation
Composer is the recommended way of installing SilverStripe modules.
```
composer require gorriecoe/silverstripe-linkfield
```

## Requirements

- gorriecoe/silverstripe-link ^1.0
- silvershop/silverstripe-hasonefield ^2.0

## Maintainers

- [Gorrie Coe](https://github.com/gorriecoe)

## usage

```php
<?php

use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;

class MyClass extends DataObject
{
    private static $has_one = [
        'Button' => Link::class
    ];

    private static $many_many = [
        'Buttons' => Link::class
    ];

    private static $many_many_extraFields = [
        'Buttons' => [
            'Sort' => 'Int' // Required for all many_many relationships
        ]
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main',
            [
                LinkField::create(
                    'Button',
                    'Button',
                    $this
                ),
                LinkField::create(
                    'Buttons',
                    'Buttons',
                    $this
                )
            ]
        );
        return $fields;
    }
}

```
