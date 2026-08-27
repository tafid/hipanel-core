<?php

declare(strict_types=1);

namespace hipanel\components;

use hipanel\helpers\Url;
use Yii;
use yii\base\Application;
use yii\base\Component;
use yii\web\View;

/**
 * A stale/mismatched session (multiple tabs, a page served from cache, a request that
 * raced an earlier request's session write, ...) makes the CSRF token embedded in the
 * page's meta tags fail validation server-side, even though nothing legitimately wrong
 * happened - see hipanel\actions\TimeZoneAction's automatic POST for the most visible
 * case of this. Rather than surface that as a dead-end 400 to every visitor who hits it,
 * fetch a fresh token from CsrfTokenAction and retry the failed request once.
 */
class CsrfRetry extends Component
{
    public function init()
    {
        parent::init();

        Yii::$app->on(Application::EVENT_BEFORE_REQUEST, function ($event) {
            /** @var View $view */
            $view = $event->sender->view;
            $csrfTokenUrl = Url::to('/site/csrf-token');
            // Yii's own CSRF-check message (Controller::beforeAction(), 'yii' category) -
            // translated per the app's language, so a plain-English match would silently
            // stop matching once the site isn't in English (this app's default language
            // is 'ru' - see config/web.php). Listing the shipped translations this app
            // actually uses is more resilient than string-matching a single locale; an
            // untranslated/new locale just falls back to today's broader (still POST +
            // same-origin gated) behavior instead of breaking.
            $csrfMessages = json_encode([
                // Source string, from yii\web\Controller::beforeAction().
                'Unable to verify your data submission.',
                // vendor/yiisoft/yii2/messages/ru/yii.php
                'Не удалось проверить переданные данные.',
                // vendor/yiisoft/yii2/messages/uk/yii.php
                'Не вдалося перевірити передані дані.',
            ]);
            $js = /** @lang JavaScript */ "
              ;(() => {
                var refreshing = false;
                var queue = [];
                var csrfMessages = $csrfMessages;
                $(document).ajaxError(function (event, jqXHR, settings) {
                  if (
                    jqXHR.status !== 400 ||
                    settings._csrfRetried ||
                    !settings.type ||
                    settings.type.toUpperCase() !== 'POST' ||
                    settings.crossDomain ||
                    settings.url.indexOf('$csrfTokenUrl') !== -1 ||
                    !jqXHR.responseJSON ||
                    csrfMessages.indexOf(jqXHR.responseJSON.message) === -1
                  ) {
                    return;
                  }
                  queue.push(settings);
                  if (refreshing) {
                    return;
                  }
                  refreshing = true;
                  $.get('$csrfTokenUrl', function (data) {
                    yii.setCsrfToken(data.csrfParam, data.csrfToken);
                    var pending = queue;
                    queue = [];
                    pending.forEach(function (queuedSettings) {
                      $.ajax($.extend({}, queuedSettings, { _csrfRetried: true }));
                    });
                  }).fail(function () {
                    queue = [];
                  }).always(function () {
                    refreshing = false;
                  });
                });
              })();
            ";
            $view->registerJs($js);
        });
    }
}
