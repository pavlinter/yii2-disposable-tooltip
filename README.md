Yii2 Dot Trasnlation
======================

![Screen Shot](https://github.com/pavlinter/yii2-dot-translation/blob/master/screenshot.png?raw=true)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist pavlinter/yii2-dot-translation "dev-master"
```

or add

```
"pavlinter/yii2-dot-translation": "dev-master"
```

to the require section of your `composer.json` file.


Configuration
-------------
* Before configure: [yii2-dot-translation](https://github.com/pavlinter/yii2-dot-translation)
* Run migration file
    ```php
    yii migrate --migrationPath=@vendor/pavlinter/yii2-disposable-tooltip/migrations
    ```
* Update controller
```php
//SiteController.php
public function actions()
{
    return [
        'disposable-hint' => [
            'class' => 'pavlinter\tooltip\HintAction',
        ]
        ...
    ];
}

```

Usage
-----
