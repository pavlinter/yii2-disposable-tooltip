<?php

/**
 * @copyright Copyright &copy; Pavels Radajevs, 2014
 * @package yii2-disposable-tooltip
 * @version 1.0.0
 */

namespace pavlinter\tooltip;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'pavlinter\tooltip\controllers';

    /**
    * @var string the name of the tooltips table.
    */
    public $userTooltipTable = '{{%user_tooltip}}';
    /**
     * @var string the name of the source_message table.
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string the cookie key.
     */
    public $cookieName = 'user_tooltip';
    /**
     * @var string the type of storage for the hint configuration
     */
    public $storage     = 'auto';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
