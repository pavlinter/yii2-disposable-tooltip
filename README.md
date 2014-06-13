Yii2 Disposable Tooltip
======================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist pavlinter/yii2-disposable-tooltip "master"
```

or add

```
"pavlinter/pavlinter/yii2-disposable-tooltip": "master"
```

to the require section of your `composer.json` file.


Configuration
-------------
* Before configure: [yii2-dot-translation](https://github.com/pavlinter/yii2-dot-translation)
* Run migration file
    ```php
    yii migrate --migrationPath=@vendor/pavlinter/yii2-disposable-tooltip/tooltip/migrations
    ```
* Update config
```php
'modules' => [
        ....
        'distooltip' => [
            'class' => 'pavlinter\tooltip\Module',
            //default
            'cookieName' => 'user_tooltip',
            'storage' => \pavlinter\tooltip\Hint::TYPE_AUTO,
            'userTooltipTable' => '{{%user_tooltip}}',
            'sourceMessageTable' => '{{%source_message}}',
        ],
        ....
    ],
```

Usage
-----
```php
<?php Hint::begin([
    'category' => 'app/hints',
    'message'  => 'Hi! I am {tip}.',
    'params' => ['tip' => 'tooltip'],
    //default
    'moduleId' => 'distooltip',
    'tag' => 'span',
    'options' => [],
    'clientOptions' => [],
    'clientEvents' => [
        'dhint.ajaxBeforeSend' => 'function($cont,id){ console.log("ajaxBeforeSend");}',
        'dhint.ajaxSuccess' => 'function($cont,id,data){ console.log("ajaxSuccess");}',
        'dhint.ajaxComplete' => 'function($cont,id,jqXHR,textStatus){ console.log("ajaxComplete");}',
        'dhint.ajaxError' => 'function($cont,id,jqXHR,textStatus,message){ console.log("ajaxError");}',
    ],
    'closeButton' => [
        'class' => 'glyphicon glyphicon-remove pull-right',
    ],
    'template' => '<div class="popover"><div class="arrow"></div><h3 class="popover-title clearfix"></h3><div class="popover-content"></div></div>',
    'title' => '',
]);?>
    <div class="content">Your content</div>
<?php Hint::end(); ?>
```