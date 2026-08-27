<?php

declare(strict_types=1);

namespace hipanel\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Forces a fresh CSRF token (discarding whatever is currently stored - see
 * Request::getCsrfToken()'s $regenerate param) and returns it as JSON, so a
 * client that just got a "Unable to verify your data submission" 400 can
 * fetch a valid token and retry instead of failing outright.
 */
class CsrfTokenAction extends Action
{
    public function run(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Per-session, must never be cached by a shared/intermediate cache or the
        // browser's back-forward cache - a stale cached token would just fail
        // validation again, defeating the whole point of this endpoint.
        Yii::$app->response->getHeaders()->set('Cache-Control', 'no-store, private');

        return [
            'csrfParam' => Yii::$app->request->csrfParam,
            // Forcing regeneration (true) here would mint a brand-new token on every
            // call, so two concurrent refresh requests (multiple tabs, several failed
            // POSTs at once) would race to overwrite each other's token in the
            // session - the token an earlier response already handed to a client
            // would go stale again before that client's retry even lands. Returning
            // the existing (or freshly-generated-only-if-missing) token instead keeps
            // it shared and valid for every concurrent caller.
            'csrfToken' => Yii::$app->request->getCsrfToken(),
        ];
    }
}
