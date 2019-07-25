<?php
/**
 * HiPanel core package
 *
 * @link      https://hipanel.com/
 * @package   hipanel-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

use hiqdev\composer\config\Builder;
use yii\web\Application;

(function () {
    require __DIR__ . '/../config/bootstrap.php';

    $host = $_SERVER['HTTP_HOST'];
    $type = (defined('HISITE_TEST') && HISITE_TEST) ? 'web-test' : 'web';
    $path = Builder::path($host . '/' . $type);
    if (!file_exists($path)) {
        $path = Builder::path($type);
    }

    $config = require $path;

    (new Application($config))->run();
})();
