<?php
use frontend\modules\server\widgets\StateFormatter;
use frontend\widgets\GridView;
use frontend\modules\server\widgets\DiscountFormatter;
use frontend\modules\object\widgets\RequestState;
use frontend\widgets\Pjax;
use frontend\widgets\Select2;
use yii\helpers\Url;
use \yii\helpers\Html;

/**
 * @var frontend\modules\server\models\OsimageSearch $osimages
 */

/**
 * @var frontend\components\View $this
 */

$this->title                   = Yii::t('app', 'Servers');
$this->params['breadcrumbs'][] = $this->title;

Pjax::begin(array_merge(Yii::$app->params['pjax'], ['enablePushState' => true]));

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'columns'      => [
        [
            'class' => 'yii\grid\CheckboxColumn',
        ],
        [
            'attribute'          => 'seller_id',
            'value'              => function ($model) {
                return Html::a($model->seller, ['/client/client/view', 'id' => $model->seller_id]);
            },
            'format'             => 'html',
            'filterInputOptions' => ['id' => 'seller_id'],
            'label'              => Yii::t('app', 'Seller'),
            'filter'             => Select2::widget([
                'attribute' => 'seller_id',
                'model'     => $searchModel,
                'url'       => Url::to(['/client/client/seller-list'])
            ]),
        ],
        [
            'attribute'          => 'client_id',
            'value'              => function ($model) {
                return Html::a($model->client, ['/client/client/view', 'id' => $model->client_id]);
            },
            'format'             => 'html',
            'filterInputOptions' => ['id' => 'author_id'],
            'label'              => Yii::t('app', 'Client'),
            'filter'             => Select2::widget([
                'attribute' => 'client_id',
                'model'     => $searchModel,
                'url'       => Url::to(['/client/client/client-all-list'])
            ]),
        ],
        [
            'attribute' => 'server_like',
            'label'     => Yii::t('app', 'Name'),
            'value'     => function ($model) {
                return $model->name;
            }
        ],
        [
            'attribute'      => 'panel',
            'format'         => 'text',
            'contentOptions' => ['class' => 'text-uppercase'],
            'value'          => function ($model) {
                return $model->panel ?: '';
            }
        ],
        'tariff',
        'tariff_note',
        [

            'attribute' => 'discounts',
            'format'    => 'raw',
            'value'     => function ($model) {
                return DiscountFormatter::widget([
                    'current' => $model->discounts['fee']['current'],
                    'next'    => $model->discounts['fee']['next'],
                ]);
            }
        ],
        [
            'attribute' => 'state',
            'format'    => 'raw',
            'value'     => function ($model) {
                return RequestState::widget([
                    'model' => $model,
                    'module' => 'server'
                ]);
            },
            'filter'    => Html::activeDropDownList($searchModel, 'state', \frontend\models\Ref::getList('state,device'), [
                'class'  => 'form-control',
                'prompt' => Yii::t('app', '--'),
            ]),
        ],
        [
            'attribute' => 'sale_time',
            'format'    => ['date'],
        ],
        [
            'attribute' => 'os',
            'format'    => 'raw',
            'value'     => function ($model) use ($osimages) {
                return frontend\modules\server\widgets\OSFormatter::widget([
                    'osimages'  => $osimages,
                    'imageName' => $model->osimage
                ]);
            }
        ],
        [
            'attribute' => 'expires',
            'format'    => 'raw',
            'value'     => function ($model) {
                return StateFormatter::widget([
                    'model' => $model
                ]);
            }
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons'  => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $model->id]);
                },
            ],
        ],
    ],
]);

Pjax::end();