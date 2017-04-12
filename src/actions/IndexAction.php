<?php
/**
 * HiPanel core package.
 *
 * @link      https://hipanel.com/
 * @package   hipanel-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2017, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\actions;

use hipanel\base\FilterStorage;
use hipanel\models\IndexPageUiOptions;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Class IndexAction.
 */
class IndexAction extends SearchAction
{
    /**
     * @var string view to render
     */
    protected $_view;

    public function setView($value)
    {
        $this->_view = $value;
    }

    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = lcfirst(Inflector::id2camel($this->id));
        }

        return $this->_view;
    }

    /**
     * @var array The map of filters for the [[hipanel\base\FilterStorage|FilterStorage]]
     */
    public $filterStorageMap = [];

    protected function getDefaultRules()
    {
        return array_merge([
            'html | pjax' => [
                'save' => false,
                'flash' => false,
                'success' => [
                    'class' => RenderAction::class,
                    'view' => $this->getView(),
                    'data' => $this->data,
                    'params' => function () {
                        return [
                            'model' => $this->getSearchModel(),
                            'dataProvider' => $this->getDataProvider(),
                            'uiModel' => $this->getUiModel(),
                        ];
                    },
                ],
            ],
        ], parent::getDefaultRules());
    }

    public function getUiModel()
    {
        return $this->controller->indexPageUiOptionsModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProvider()
    {
        if ($this->dataProvider === null) {
            $request = Yii::$app->request;

            $formName = $this->getSearchModel()->formName();
            $requestFilters = $request->get($formName) ?: $request->get() ?: $request->post();

            // Don't save filters for ajax requests, because
            // the request is probably triggered with select2 or smt similar
            if ($request->getIsPjax() || !$request->getIsAjax()) {
                $filterStorage = new FilterStorage(['map' => $this->filterStorageMap]);

                if ($request->getIsPost() && $request->post('clear-filters')) {
                    $filterStorage->clearFilters();
                }

                $filterStorage->set($requestFilters);

                // Apply filters from storage only when request does not contain any data
                if (empty($requestFilters)) {
                    $requestFilters = $filterStorage->get();
                }
            }

            $search = ArrayHelper::merge($this->findOptions, $requestFilters);

            $this->returnOptions[$this->controller->modelClassName()] = ArrayHelper::merge(
                ArrayHelper::remove($search, 'return', []),
                ArrayHelper::remove($search, 'rename', [])
            );

            if ($formName !== '') {
                $search = [$formName => $search];
            }
            $this->dataProvider = $this->getSearchModel()->search($search, $this->dataProviderOptions);

            if ($this->getUiModel()->sort) {
                $attribute = $this->getUiModel()->sortAttribute;
                $direction = $this->getUiModel()->sortDirection;
                $this->dataProvider->setSort(['defaultOrder' => [$attribute => $direction]]);
            }
        }

        return $this->dataProvider;
    }
}
