<?php

namespace mdm\filter;

use Yii;
use yii\base\DynamicModel;

/**
 * VerifyController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class VerifyController extends \yii\web\Controller
{
    public $defaultAction = 'verify';

    /**
     * @var EnterCode 
     */
    public $filter;

    public function actionVerify()
    {
        $session = Yii::$app->session;
        $urlKey = $this->filter->buildKey($this->filter->returnUrlParam);
        $urls = $session->get($urlKey);
        if (is_array($urls) && isset($urls[0], $urls[1])) {
            $route = $urls[0];
            $returnUrl = $urls[1];
        } else {
            throw new \yii\base\InvalidCallException();
        }
        $key = $this->filter->buildKey($route);
        $field = 'f' . substr($key, 0, 10);
        $model = new DynamicModel([$field]);
        $model->addRule($field, 'required');

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($this->filter->isValid($model->$field, $route)) {
                $this->filter->setValid($route);
                return Yii::$app->getResponse()->redirect($returnUrl);
            } else {
                $model->addError($field, $this->filter->message);
            }
        }
        return $this->render('verify', ['model' => $model, 'field' => $field]);
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return __DIR__ . '/views';
    }
}