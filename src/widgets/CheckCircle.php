<?php

namespace hipanel\widgets;

use hipanel\modules\client\models\Verification;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

class CheckCircle extends Widget
{
    const COLOR_VERIFIED = '#00a65a';
    const COLOR_CONFIRMED = '#54b5ff';
    const COLOR_UNCONFIRMED = '#b3b3b3';

    /**
     * @var Verification
     */
    public $model;

    public function init()
    {
        if (!$this->model instanceof Verification) {
            throw new InvalidConfigException(Yii::t('hipanel', 'Attribute "model" must be instance of Verification object'));
        }
    }

    public function run()
    {
        return Html::tag('i', null, [
            'class' => 'fa fa-fw fa-lg fa-check-circle pull-right verification-tooltip',
            'style' => 'color: ' . $this->getColor(),
            'title' => $this->model->getLabels()[$this->model->level],
            'data' => [
                'toggle' => 'tooltip',
                'trigger' => 'hover',
            ]
        ]);
    }

    protected function getColor()
    {
        $color = self::COLOR_UNCONFIRMED;
        switch ($this->model) {
            case $this->model->isVerified():
                $color = self::COLOR_VERIFIED;
                break;
            case $this->model->isConfirmed():
                $color = self::COLOR_CONFIRMED;
                break;
        }

        return $color;
    }
}