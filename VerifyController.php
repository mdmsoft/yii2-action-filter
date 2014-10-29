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
    public $validationCallback;
    public $returnUrlParam;
    public $validationKey;

    public function actionVerify($for)
    {
        $key = md5(serialize([$this->module->uniqueId, $for]));
        $field = 'f' . substr($key, 0, 10);
        $model = new DynamicModel([$field]);
        $model->addRule($field, 'required');

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if (call_user_func($this->validationCallback, $model->$field, $key)) {
                $verify = Yii::$app->security->hashData(time(), $this->validationKey);
                $session = Yii::$app->session;
                $session->set($key, $verify);
                $returnUrl = $session->get(md5(serialize([$this->returnUrlParam, $for])));
                return Yii::$app->getResponse()->redirect($returnUrl);
            } else {
                $model->addError($field, 'Code invalid');
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
