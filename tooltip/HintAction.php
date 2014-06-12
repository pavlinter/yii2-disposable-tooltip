<?php

/**
 * @copyright Copyright &copy; Pavels Radajevs, 2014
 * @package yii2-disposable-tooltip
 * @version 1.0.0
 */

namespace pavlinter\tooltip;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;



class HintAction extends Action
{
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
    public $storage = 'auto';
    /**
     * Initializes the action.
     * @throws InvalidConfigException if the font file does not exist.
     */
    public function init()
    {

    }
    /**
     * Runs the action.
     */
    public function run()
    {

        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            return;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = (int)Yii::$app->request->post('id');

        if (Yii::$app->user->isGuest || $this->storage === 'cookie') {
            $tooltips = Yii::$app->request->cookies->getValue($this->cookieName);
            if (!is_array($tooltips)) {
                $tooltips = [];
            }

            if (!isset($tooltips[$id])) {
                $tooltips[$id] = 1;
                $options['name'] = $this->cookieName;
                $options['value'] = $tooltips;
                $options['expire'] = time()+86400*365;
                $cookie = new \yii\web\Cookie($options);
                Yii::$app->response->cookies->add($cookie);
            }

        } else {
            $query = new Query();
            $res = $query->from($this->userTooltipTable)->where([
                'id_user' => Yii::$app->getUser()->getId(),
                'id_source_message' => $id,
            ])->exists();

            if (!$res) {
                Yii::$app->db->createCommand()->insert($this->userTooltipTable, [
                    'id_user' => Yii::$app->getUser()->getId(),
                    'id_source_message' => $id,
                ])->execute();
            }
        }
        return ['r'=>1];
    }
}
