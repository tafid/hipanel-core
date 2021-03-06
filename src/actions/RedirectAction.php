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

use Closure;
use Yii;
use yii\helpers\Url;

/**
 * @property array url the URL for redirect. Every element can be a callback, which gets the model and $this pointer as arguments
 */
class RedirectAction extends Action
{
    /**
     * @var string|array url to redirect to
     */
    protected $_url;

    /**
     * Collects the URL array, executing callable functions.
     *
     * @return string|array default return to previous page (referer)
     */
    public function getUrl()
    {
        if ($this->_url instanceof Closure) {
            return call_user_func($this->_url, $this);
        }
        return $this->_url ?: Yii::$app->request->referrer;
    }

    /**
     * @param $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function run()
    {
        if ($this->success) {
            $this->addFlash('success', $this->success);
        } elseif ($this->error) {
            $this->addFlash('error', $this->error);
        }
        return $this->controller->redirect($this->getUrl());
    }

    public $error;
    public $success;
}
