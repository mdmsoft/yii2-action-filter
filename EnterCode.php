<?php

namespace mdm\filter;

use Yii;
use yii\base\Module;
use yii\base\InvalidConfigException;

/**
 * EnterCode
 * Attach to Module or Application
 * 
 * ~~~
 * 'as filter' => [
 *     'class' => 'mdm\filter\EnterCode',
 *     'timeout' => 600, // default 300
 *     'validationCallback' => function ($code, $actionId) {
 *         return $code === 'bismillah';
 *     },
 *     'only' => [
 *         'default/view', // actions
 *     ]
 * ]
 * ~~~
 *
 * @property \yii\base\Module $owner
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class EnterCode extends \yii\base\ActionFilter
{
    /**
     * @var string 
     */
    public $verificationRoute = 'verify';

    /**
     * @var Closure function use to validate
     * 
     * ```php
     * function($code, $actionId){
     *     // check
     *     return true;
     * }
     * ``` 
     */
    public $validationCallback;

    /**
     * @var integer validation time in second
     */
    public $timeout = 300;

    /**
     * @var string key to validate session variable.
     * If null mean no validation
     */
    public $validationKey;

    /**
     * @var boolean  
     */
    public $renewSession = true;

    /**
     * @var boolean  
     */
    public $validateForAll = false;

    /**
     * @var string message when code entered is invalid.
     */
    public $message;

    /**
     * @var string file to be rendered
     */
    public $viewFile = '@mdm/filter/views/verify.php';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->validationCallback === null) {
            throw new InvalidConfigException('$validationCallback must be set');
        }
        if ($this->message === null) {
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
                'viewFile' => $this->viewFile,
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

        $verify = Yii::$app->session->get($this->buildKey($this->validateForAll ? '' : $route));
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

    /**
     * Redirect to verify controller
     * @param string $route
     * @return \yii\web\Response
     */
    protected function verifyRequired($route)
    {
        $request = Yii::$app->getRequest();
        Yii::$app->session->set($this->buildKey('_url'), [$route, $request->getUrl()]);
        $id = $this->owner->uniqueId . '/' . $this->verificationRoute;
        return Yii::$app->getResponse()->redirect([$id]);
    }

    /**
     * Build session key
     * @param type $key
     * @return type
     */
    public function buildKey($key)
    {
        return md5(serialize([__CLASS__, $this->owner->uniqueId, $key]));
    }

    /**
     * Check entered code for action
     * @param string $code
     * @param string $route
     * @return boolean
     */
    public function isValid($code, $route)
    {
        return call_user_func($this->validationCallback, $code, $this->validateForAll ? '' : $route);
    }

    /**
     * Set session
     * @param string $route
     */
    public function setValid($route)
    {
        if (!empty($this->validationKey)) {
            $verify = Yii::$app->security->hashData(time(), $this->validationKey);
        } else {
            $verify = time();
        }
        Yii::$app->session->set($this->buildKey($this->validateForAll ? '' : $route), $verify);
    }
}