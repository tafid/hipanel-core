<?php

declare(strict_types=1);
/**
 * HiPanel core package
 *
 * @link      https://hipanel.com/
 * @package   hipanel-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\components;

use yii\base\InvalidConfigException;

/**
 * Extends the base Yii2 request with support for the HTTP QUERY method —
 * a safe, idempotent method that carries its query in the request body.
 *
 * @see https://www.ietf.org/archive/id/draft-ietf-httpbis-safe-method-w-body-04.html
 */
class Request extends \yii\web\Request
{
    public function init()
    {
        parent::init();

        if (!in_array('QUERY', $this->csrfTokenSafeMethods, true)) {
            $this->csrfTokenSafeMethods[] = 'QUERY';
        }
    }

    /**
     * For QUERY requests, merges the parsed request body into the query params,
     * with body values taking precedence over the URL query string on key collision.
     * Behavior for all other methods is unchanged from the parent class.
     *
     * @return array the request GET parameter values.
     * @throws InvalidConfigException
     */
    public function getQueryParams()
    {
        $params = parent::getQueryParams();

        if ($this->getMethod() === 'QUERY') {
            $params = array_merge($params, (array)$this->getBodyParams());
        }

        return $params;
    }
}
