<?php

namespace hipanel\actions;

use Closure;
use Yii;

/**
 * Class ViewAction
 */
class ViewAction extends Action
{
    /**
     * @var string view to render.
     */
    public $view = 'view';

    /**
     * @var array|Closure additional data passed to view
     */
    public $data = [];

    /**
     * @var array additional data passed to model find method
     */
    public $findOptions = [];

    public function findModel($id)
    {
        return $this->controller->findModel(array_merge(['id' => $id], $this->findOptions));
    }

    public function prepareData($id)
    {
        if ($this->data instanceof Closure) {
            return call_user_func($this->data, $this, $id);
        } else {
            return $this->data;
        }
    }

    public function run($id)
    {
        $model = $this->findModel($id);
        $this->collection->set($model);

        return $this->controller->render('view', array_merge([
            'model' => $model,
        ], $this->prepareData($id)));
    }
}