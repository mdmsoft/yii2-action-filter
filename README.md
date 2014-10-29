yii2-action-filter
==================

Force entry code to enter to the action.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft/yii2-action-filter "*"
```

for dev-master

```
php composer.phar require mdmsoft/yii2-action-filter "dev-master"
```

or add

```
"mdmsoft/yii2-action-filter": "*"
```

to the require section of your `composer.json` file.


Usage
-----

After instalation done. Add to your config

```php
    ...
    'as access' => [
        'class' => 'mdm\filter\EnterCode',
        'timeout' => 600, // default 300
        'validationCallback' => function ($code, $actionId) {
            return $code === 'bismillah';
        },
        'only' => [
            'default/view', // actions
        ]
    ],

```

You can attach filter to application or module.
