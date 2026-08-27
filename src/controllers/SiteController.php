<?php
/**
 * HiPanel core package
 *
 * @link      https://hipanel.com/
 * @package   hipanel-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\controllers;

use hipanel\actions\CsrfTokenAction;
use hipanel\actions\TimeZoneAction;
use hipanel\helpers\UserHelper;
use hipanel\logic\Impersonator;
use hipanel\models\User;
use hisite\actions\RedirectAction;
use hisite\actions\RenderAction;
use Yii;
use hiam\authclient\AuthAction;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Site controller.
 */
class SiteController extends \hisite\controllers\SiteController
{
    /** @var string */
    public $defaultAuthClient = 'hiam';
    /**
     * @var Impersonator
     */
    private $impersonator;

    public function __construct(string $id, Module $module, Impersonator $impersonator, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->impersonator = $impersonator;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginRequired' => [
                'class' => AccessControl::class,
                'only' => ['profile', 'notification-settings', 'index'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            // 'csrf-token' is documented as GET-only below (in actions()) but nothing
            // enforced that - a POST here with a still-valid token would slip past
            // Yii's own CSRF check and reach the action anyway, regenerating the
            // token as an unintended side effect of a request that was never
            // supposed to route here.
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'csrf-token' => ['get'],
                ],
            ],
        ]);
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
            'impersonate-auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this->impersonator, 'impersonateUser'],
            ],
            'index' => [
                'class' => RedirectAction::class,
                'url'   => ['/dashboard/dashboard'],
            ],
            'profile' => [
                'class' => RedirectAction::class,
                'url' => [
                    '@client/view',
                    'id' => UserHelper::getId(),
                ],
            ],
            'lockscreen' => [
                'class' => RenderAction::class,
            ],
            'ip-restriction-settings' => [
                'class' => RedirectAction::class,
                'url'   => [
                    '@client/view',
                    'id'    => UserHelper::getId(),
                    '#'     => 'ip_restriction_settings',
                ],
            ],
            'notification-settings' => [
                'class' => RedirectAction::class,
                'url'   => [
                    '@client/view',
                    'id'    => UserHelper::getId(),
                    '#'     => 'notification_settings',
                ],
            ],
            'timezone' => TimeZoneAction::class,
            // GET, not POST - deliberately: it needs to be reachable even when the client's
            // current CSRF token has gone stale/mismatched (that's the whole point of it),
            // and GET requests aren't subject to Yii's CSRF check to begin with.
            'csrf-token' => CsrfTokenAction::class,
        ]);
    }

    public function actionUnimpersonate()
    {
        if ($this->impersonator->isUserImpersonated()) {
            $this->impersonator->unimpersonateUser();
        }

        return $this->redirect('/');
    }

    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();
        $user = new User();
        foreach ($user->attributes() as $k) {
            if (isset($attributes[$k])) {
                $user->{$k} = $attributes[$k];
            }
        }
        $user->save();
        Yii::$app->user->login($user, Yii::$app->params['login_duration'] ?? 3600 * 24 * 30);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/site/index']);
        }

        return $this->redirect(['/site/auth', 'authclient' => $this->defaultAuthClient]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        $back = Yii::$app->request->getHostInfo();
        $url = Yii::$app->authClientCollection->getClient()->buildUrl('site/logout', compact('back'));

        return $this->redirect($url);
    }

    public function actionSignup()
    {
        $back = Yii::$app->request->getHostInfo();
        $url = Yii::$app->authClientCollection->getClient()->buildUrl('site/signup', compact('back'));

        return $this->redirect($url);
    }

    public function actionPushImpersonateAuth()
    {
        if ($this->impersonator->isUserImpersonated()) {
            $this->impersonator->unimpersonateUser();
        }
        $this->impersonator->backupCurrentToken();
        $this->impersonator->impersonateWithStateAndCode(
            $this->request->get('code'),
            $this->request->get('state')
        );

        return $this->goHome();
    }

    public function actionImpersonate($user_id)
    {
        if ($this->impersonator->isUserImpersonated()) {
            $this->impersonator->unimpersonateUser();
        }

        $this->impersonator->backupCurrentToken();

        return $this->redirect($this->impersonator->buildAuthUrl($user_id));
    }

    public function actionHealthcheck()
    {
        $text = 'Up and running.';
        $text .= $this->testCache();
        if (isset(Yii::$app->user->identity->id)) {
            $id = Yii::$app->user->identity->id;
            $text .= "\n<h6>User ID: <userId>$id</userId></h6>";
        }

        return $text;
    }

    private function testCache(): string
    {
        $cache = Yii::$app->cache;
        if (!empty($cache)) {
            $cache->set('test_cache', 'test');
            $testCache = $cache->get('test_cache');
        }
        if (isset($testCache) && $testCache === 'test') {
            $result = "\n<h6>Cache is OK</h6>";
        } else {
            $result = "\n<h6>Cache is ABSENT</h6>";
        }
        return $result;
    }
}
