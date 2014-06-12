<?php

/**
 * @copyright Copyright &copy; Pavels Radajevs, 2014
 * @package yii2-disposable-tooltip
 * @version 1.0.0
 */

namespace pavlinter\tooltip;

use Yii;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\base\InvalidConfigException;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\JsExpression;


class Hint extends \yii\base\Widget
{
    const TYPE_AUTO = 'auto';
    const TYPE_COOKIE = 'cookie';

    public static $loaded = false;
    public static $showBg = false;
    /**
     * @var string the name of the tooltips table.
     */
    public $userTooltipTable = '{{%user_tooltip}}';
    /**
     * @var string the cookie key.
     */
    public $cookieName = 'user_tooltip';
    /**
     * @var string the type of storage for the hint configuration
     */
    public $storage     = 'auto';
    /**
     * @var string tag a round content
     */
    public $tag         = 'span';
    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    public $category    = null;
    public $message     = null;
    public $params      = [];
    /**
     * @var array the options for the js.
     */
    public $clientOptions = [];
    /**
     * @var array the event handlers for the js.
     * For example you could write the following in your widget configuration:
     *
     * 'clientEvents' => [
     *  'dhint.ajaxBeforeSend' => 'function () {}'
     * ],
     */
    public $clientEvents = [];
    /**
     * @var array the HTML attributes for the close button.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $closeButton = [
        'class' => 'glyphicon glyphicon-remove pull-right',
    ];
    /**
     * @var array || function the template for popover.
     */
    public $template = '<div class="popover"><div class="arrow"></div><h3 class="popover-title clearfix"></h3><div class="popover-content"></div></div>';
    /**
     * @var string the title for popover.
     */
    public $title    = '';
    public $sourceMessageTable = '{{%source_message}}';
    public $router             = 'site/disposable-hint';
    /**
     * @var string the content for popover.
     */
    public $content            = null;

    private $messageId  = false;
    /**
     * Initializes the component by configuring the default message categories.
     */
    public function init()
    {
        parent::init();


        if (empty($this->category)) {
            throw new InvalidConfigException('The "category" property must be set.');
        }
        if (empty($this->message)) {
            throw new InvalidConfigException('The "message" property must be set.');
        }

        $view = $this->getView();
        $message = Yii::t($this->category,$this->message,$this->params);
        $this->messageId = Yii::$app->i18n->getMessageId($this->category,$this->message);

        //insert if not exist message
        if ($this->messageId === false && $this->sourceMessageTable) {
            $command = Yii::$app->db->createCommand()->insert($this->sourceMessageTable,[
                'category' => $this->category,
                'message'  => $this->message,
            ])->execute();
            $this->messageId = Yii::$app->db->lastInsertID;
        }

        if ($this->read()) {
            return true;
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        Html::addCssClass($this->closeButton,'disposable-hint-btn');
        $this->closeButton['href'] = 'javascript:void(0);';
        $this->closeButton['data-trigger'] = '#' . $this->options['id'];
        $this->options['data-id'] = $this->messageId;

        //javascript clientOptions
        $this->clientOptions = ArrayHelper::merge([
            'animation' => true,
            'container' => false,
            'html' => true,
            'placement' => 'auto top',
            'queue' => false,
            'strollToPopover' => false,
            'strollToFirstPopover' => false,
            'showBg' => false,
        ],$this->clientOptions);
        $this->clientOptions['template']    = $this->getTemplate();
        $this->clientOptions['title']       = $this->getTitle();
        $this->clientOptions['trigger']     = 'manual';


        $this->clientEvents = [
            'dhint.ajaxBeforeSend' => 'function(e,$cont,id){
                console.log(e);
                console.log($cont);
                console.log(id);
            }',
        ];


        if ($this->content === null) {
            $this->content = function ($message,$btn) {
                return $btn.$message;
            };
        }

        if (is_callable($this->content)) {
            $this->clientOptions['content'] = call_user_func($this->content,$message,Html::tag('a','',$this->closeButton));
        } else {
            $this->clientOptions['content'] = $message;
        }


        echo Html::beginTag($this->tag,$this->options);
        $this->registerAssets($view);



    }
    public function run()
    {
        echo Html::endTag($this->tag);
    }
    /**
     * Register client side
     */
    public function registerAssets($view)
    {
        BootstrapPluginAsset::register($view);
        $queue                  = ArrayHelper::remove($this->clientOptions,'queue');
        $strollToPopover        = ArrayHelper::remove($this->clientOptions,'strollToPopover');
        $strollToFirstPopover   = ArrayHelper::remove($this->clientOptions,'strollToFirstPopover');
        $showBg                 = ArrayHelper::remove($this->clientOptions,'showBg');
        $scrollOptions          = ArrayHelper::remove($this->clientOptions,'scrollOptions',[]);

        $scrollOptions = ArrayHelper::merge([
            'duration' => 500,
        ],$scrollOptions);
        $id = $this->options['id'];


        $script = '$("#'.$id.'").popover(' . Json::encode($this->clientOptions) . ')
                        .on("hidden.bs.popover", function () {
                                if(disposableHintQueue.length && ' . ($queue?1:0) . '){
                                    var $next       = $(disposableHintQueue.shift()).popover("show");
                                    ' . ($strollToPopover?'disposableHintScrollTo($next);':'') . '
                                }else{
                                    $(".bg-disposable-hint").removeClass("show").hide();
                                }
                        });';


        foreach ($this->clientEvents as $event => $handler) {
            $script .= '$("#'.$id.'").on("dhint.' . $event . '" ,' . new JsExpression($handler) . ')';
        }

        if ($queue) {
            $script .= '
                disposableHintQueue.push("#'.$id.'");
            ';
        } else {
            $script .= '
                $("#'.$id.'").popover("show");
            ';
        }
        $view->registerJs($script);


        if (self::$showBg === false && $showBg) {
            $view->on($view::EVENT_END_BODY, function ($event) {
                echo Html::tag('div','',[
                    'class' => 'bg-disposable-hint modal-backdrop fade in show',
                ]);
            });
            self::$showBg = true;
        }


        if (self::$loaded === false) {
            $script = '';
            if ($strollToFirstPopover) {
                $script .= '
                    if(disposableHintQueue.length){
                        disposableHintScrollTo($(disposableHintQueue.shift()).popover("show"));
                    }
                ';
            } else {
                $script .= '
                    if(disposableHintQueue.length){
                        $(disposableHintQueue.shift()).popover("show");
                    }
                ';
            }


            $script .= '
                $(document).on("click",".disposable-hint-btn",function(){
                        var $cont = $($(this).attr("data-trigger"));
                        var id    = parseInt($cont.attr("data-id"));
                        if(!id){
                            return false;
                        }
                        $.ajax({
                            url: "' . Url::to([$this->router]) . '",
                            type: "POST",
                            dataType: "json",
                            data: {id:id},
                            beforeSend: function(){
                                $cont.popover("hide");
                                $("#' . $id . '").trigger("dhint.ajaxBeforeSend",[$cont,id]);
                            },
                            success: function(d){
                               $("#' . $id . '").trigger("dhint.ajaxSuccess",[$cont,id,d]);
                            },
                            complete:function(jqXHR,textStatus){
                                $("#' . $id . '").trigger("dhint.ajaxComplete",[$cont,id,jqXHR,textStatus]);
                            },
                            error:function(jqXHR,textStatus,message){
                                 $("#' . $id . '").trigger("dhint.ajaxError",[$cont,id,jqXHR,textStatus,message]);
                            },
                        });

                        return false;
                });

            ';


            $view->registerJs($script);

            $view->registerJs('
                var disposableHintQueue = [];
                var disposableHintScrollTo = function($next){
                    var settings    = $next.data("bs.popover");
                    var $tip        = settings.$tip;
                    var top         = $tip.offset().top - ($(window).height() - $tip.outerHeight(true))/2;
                    $("html").animate({scrollTop : top},' . Json::encode($scrollOptions) . ');
                }
            ',$view::POS_HEAD);
            self::$loaded = true;
        }

    }

    public function getTemplate()
    {
        if (is_callable($this->template)) {
            return call_user_func($this->template,Html::tag('a','',$this->closeButton));
        }
        return $this->template;

    }
    public function getTitle()
    {
        if (is_callable($this->title)) {
            return call_user_func($this->title,Html::tag('a','',$this->closeButton));
        }
        return $this->title;

    }
    public function read()
    {
        static $dataCookie;
        static $dataDb;
        if (Yii::$app->user->isGuest || $this->storage === 'cookie') {
            if ($dataCookie === null) {
                $tooltips = Yii::$app->request->cookies->getValue($this->cookieName);
                if (!is_array($tooltips)) {
                    $tooltips = [];
                }
                $dataCookie = $tooltips;
            }

            if (isset($dataCookie[$this->messageId])) {
                return true;
            }
        } else {
            if ($dataDb === null) {
                $query = new Query();
                $dataDb = $query->from($this->userTooltipTable)->select(['id_source_message'])->where([
                    'id_user' => Yii::$app->getUser()->getId(),
                ])->indexBy('id_source_message')->all();
            }
            if (isset($dataDb[$this->messageId])) {
                return true;
            }
        }
        return false;
    }

}
