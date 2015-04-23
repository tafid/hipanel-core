<?php

use frontend\components\Re;
use frontend\components\grid\GridView;
use frontend\components\grid\CheckboxColumn;
use frontend\modules\client\grid\ClientColumn;
use frontend\components\grid\EditableColumn;
use frontend\modules\client\grid\ResellerColumn;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title                    = Yii::t('app', 'Payments');
$this->params['breadcrumbs'][]  = $this->title;
$this->params['subtitle']       = Yii::$app->request->queryParams ? 'filtered list' : 'full list';

?>

<div class="box box-primary">
<div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'columns'      => [
        [
            'class'                 => CheckboxColumn::className(),
        ],
        [
            'class'                 => ResellerColumn::className(),
        ],
        [
            'class'                 => ClientColumn::className(),
        ],
        [
            'attribute'             => 'time',
            'format'                => 'datetime',
        ],
        [
            'attribute'             => 'sum',
            'filter'                => false,
            'format'                => 'html',
            'contentOptions'        => ['align' => 'right'],
            'value'                 => function ($model) {
                return Html::tag('b','$ '.$model->sum);
            },
        ],
        [
            'attribute'             => 'balance',
            'filter'                => false,
            'contentOptions'        => ['align' => 'right'],
            'value'                 => function ($model) {
                return '$ '.$model->balance;
            },
        ],
        [
            'attribute'             => 'descr',
            'format'                => 'html',
            'value'                 => function ($model) {
                return Html::tag('b',Re::l($model->type_label)).' '.$model->object;
            },
        ],
    ],
]) ?>
</div>
</div>