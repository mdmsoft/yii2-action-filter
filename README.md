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

After instalation done. Attach filter to Module or Application

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

You can cutomize view of verify controler by setting property `viewFile`

```php
    ...
    'as access' => [
        'class' => 'mdm\filter\EnterCode',
        'viewFile' => '@your/views/verify.php',
        ...

```
