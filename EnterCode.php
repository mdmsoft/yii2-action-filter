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
    public $validationKey = 'x';

    /**
    * @inheritdoc
    */
    public function init()
    {
        if ($this->validationCallback === null) {
            throw new InvalidConfigException('$validationCallback must be set');
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
                'validationCallback' => $this->validationCallback,
                'returnUrlParam' => $this->returnUrlParam,
                'validationKey' => $this->validationKey,
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
        $uid = $action->uniqueId;

        $key = md5(serialize([$this->owner->uniqueId, $uid]));
        $verify = Yii::$app->session->get($key);
        if (is_string($verify) && ($verify = Yii::$app->security->validateData($verify, $this->validationKey)) !== false && $verify > time() - $this->timeout) {
            return true;
        } else {
            $this->verifyRequired($uid);
        }
    }

    protected function verifyRequired($for)
    {
        $request = Yii::$app->getRequest();
        Yii::$app->session->set(md5(serialize([$this->returnUrlParam, $for])), $request->getUrl());
        $mid = $this->owner->uniqueId . '/' . $this->verificationRoute;
        return Yii::$app->getResponse()->redirect([$mid, 'for' => $for]);
    }
}
