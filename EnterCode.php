<?php

namespace mdm\filter;

use Yii;
use yii\base\Module;
use yii\base\InvalidConfigException;

/**
 * EnterCode
 *
 * @property \yii\base\Module $owner
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class EnterCode extends \yii\base\ActionFilter
{
    public $verificationRoute = 'verify';
    public $validationCallback;
    public $timeout = 300;
    public $returnUrlParam = '__return_url';
    public $validationKey;
    public $renewSession = true;
    public $message;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->validationCallback === null) {
            throw new InvalidConfigException('$validationCallback must be set');
        }
        if($this->message === null){
            $this->message = "Code invalid";
        }
        $this->except[] = $this->verificationRoute . '/verify';
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        if ($owner instanceof Module) {
            $owner->controllerMap[$this->verificationRoute] = [
                'class' => __NAMESPACE__ . '\VerifyController',
                'filter' => $this,
            ];
            parent::attach($owner);
        } else {
            throw new InvalidConfigException(static::className() . '::$owner must instanceof yii\base\Module');
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $route = $action->uniqueId;

        $verify = Yii::$app->session->get($this->buildKey($route));
        if (!empty($this->validationKey)) {
            $verify = is_string($verify) ? Yii::$app->security->validateData($verify, $this->validationKey) : false;
        }
        if ($verify < time() - $this->timeout) {
            return $this->verifyRequired($route);
        }

        if ($this->renewSession) {
            $this->setValid($route);
        }
        return true;
    }

    protected function verifyRequired($route)
    {
        $request = Yii::$app->getRequest();
        Yii::$app->session->set($this->buildKey($this->returnUrlParam), [$route, $request->getUrl()]);
        $id = $this->owner->uniqueId . '/' . $this->verificationRoute;
        return Yii::$app->getResponse()->redirect([$id]);
    }

    public function buildKey($key)
    {
        return md5(serialize([__CLASS__, $this->owner->uniqueId, $key]));
    }

    public function isValid($code, $route)
    {
        return call_user_func($this->validationCallback, $code, $route);
    }

    public function setValid($route)
    {
        if (!empty($this->validationKey)) {
            $verify = Yii::$app->security->hashData(time(), $this->validationKey);
        } else {
            $verify = time();
        }
        Yii::$app->session->set($this->buildKey($route), $verify);
    }
}