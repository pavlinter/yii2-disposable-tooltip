<?php

/**
 * @copyright Copyright &copy; Pavels Radajevs, 2014
 * @package yii2-disposable-tooltip
 * @version 1.0.0
 */

namespace pavlinter\tooltip\controllers;

use pavlinter\tooltip\Hint;
use yii\web\Controller;
use yii\db\Query;
use yii\filters\VerbFilter;
use Yii;
use yii\web\Response;

/**
 * Class DefaultController
 * @package pavlinter\tooltip\controllers
 */
class DefaultController extends Controller
{
    public $defaultAction = 'add-hint';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post'],
                ],
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function actionAddHint()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = (int)Yii::$app->request->post('id');

        if (Yii::$app->user->isGuest || $this->module->storage === Hint::TYPE_COOKIE) {
            $tooltips = Yii::$app->request->cookies->getValue($this->module->cookieName);
            if (!is_array($tooltips)) {
                $tooltips = [];
            }

            if (!isset($tooltips[$id])) {
                $tooltips[$id] = 1;
                $options['name'] = $this->module->cookieName;
                $options['value'] = $tooltips;
                $options['expire'] = time()+86400*365;
                $cookie = new \yii\web\Cookie($options);
                Yii::$app->response->cookies->add($cookie);
            }

        } else {
            $query = new Query();
            $res = $query->from($this->module->userTooltipTable)->where([
                'user_id' => Yii::$app->getUser()->getId(),
                'source_message_id' => $id,
            ])->exists();

            if (!$res) {
                Yii::$app->db->createCommand()->insert($this->module->userTooltipTable, [
                    'user_id' => Yii::$app->getUser()->getId(),
                    'source_message_id' => $id,
                ])->execute();
            }
        }
        return ['r'=>1];
    }
}