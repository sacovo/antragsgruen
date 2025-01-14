<?php

namespace app\controllers;

use app\components\ConsultationAccessPassword;
use app\components\HTMLTools;
use app\components\UrlHelper;
use app\models\exceptions\Internal;
use app\models\settings\AntragsgruenApp;
use app\models\settings\Layout;
use app\models\db\Amendment;
use app\models\db\Consultation;
use app\models\db\Motion;
use app\models\db\Site;
use app\models\db\User;
use Yii;
use yii\base\Module;
use yii\helpers\Html;
use yii\web\Controller;

class Base extends Controller
{
    /** @var Layout */
    public $layoutParams = null;

    /** @var null|Consultation */
    public $consultation = null;

    /** @var null|Site */
    public $site = null;

    /** @var string */
    public $layout = '@app/views/layouts/column1';

    /**
     * @param string $cid the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($cid, $module, $config = [])
    {
        parent::__construct($cid, $module, $config);

        // Hint: can be overwritten in loadConsultation
        $this->layoutParams = new Layout();
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws Internal
     * @throws \Exception
     * @throws \yii\base\ExitException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Yii::$app->response->headers->add('X-Xss-Protection', '1');
        Yii::$app->response->headers->add('X-Content-Type-Options', 'nosniff');
        Yii::$app->response->headers->add('X-Frame-Options', 'sameorigin');

        if (!parent::beforeAction($action)) {
            return false;
        }

        $params = Yii::$app->request->resolve();
        /** @var AntragsgruenApp $appParams */
        $appParams = Yii::$app->params;

        if ($appParams->updateKey) {
            $this->showErrorpage(503, Yii::t('base', 'err_update_mode'));
        }

        $inManager   = (get_class($this) === ManagerController::class);
        $inInstaller = (get_class($this) === InstallationController::class);

        if ($appParams->siteSubdomain) {
            if (strpos($appParams->siteSubdomain, 'xn--') === 0) {
                HTMLTools::loadNetIdna2();
                $idna      = new \Net_IDNA2();
                $subdomain = $idna->decode($appParams->siteSubdomain);
            } else {
                $subdomain = $appParams->siteSubdomain;
            }

            $consultation = (isset($params[1]['consultationPath']) ? $params[1]['consultationPath'] : '');
            if ($consultation === '' && $this->isGetSet('passConId')) {
                $consultation = Yii::$app->request->get('passConId');
            }
            $this->loadConsultation($subdomain, $consultation);
            if ($this->site) {
                $this->layoutParams->setLayout($this->site->getSettings()->siteLayout);
            } else {
                $this->layoutParams->setLayout(Layout::getDefaultLayout());
            }
        } elseif (isset($params[1]['subdomain'])) {
            if (strpos($params[1]['subdomain'], 'xn--') === 0) {
                HTMLTools::loadNetIdna2();
                $idna      = new \Net_IDNA2();
                $subdomain = $idna->decode($params[1]['subdomain']);
            } else {
                $subdomain = $params[1]['subdomain'];
            }

            $consultation = (isset($params[1]['consultationPath']) ? $params[1]['consultationPath'] : '');
            if ($consultation === '' && $this->isGetSet('passConId')) {
                $consultation = Yii::$app->request->get('passConId');
            }
            $this->loadConsultation($subdomain, $consultation);
            if ($this->site) {
                $this->layoutParams->setLayout($this->site->getSettings()->siteLayout);
            } else {
                $this->layoutParams->setLayout(Layout::getDefaultLayout());
            }
        } elseif (!($inInstaller || $inManager) && !$appParams->multisiteMode) {
            $this->layoutParams->setLayout(Layout::getDefaultLayout());
            $this->showErrorpage(500, Yii::t('base', 'err_no_site_internal'));
        } else {
            $this->layoutParams->setLayout(Layout::getDefaultLayout());
        }

        // Login and Mainainance mode is always allowed
        if (get_class($this) === UserController::class) {
            return true;
        }

        if (get_class($this) === PagesController::class && in_array($action->id, ['show-page', 'css'])) {
            return true;
        }
        if (get_class($this) === PagesController::class && $action->id === 'file' && $this->consultation) {
            $logo = basename($this->consultation->getSettings()->logoUrl);
            if ($logo && isset($params[1]) && isset($params[1]['filename']) && $params[1]['filename'] === $logo) {
                return true;
            }
        }

        if (get_class($this) === ConsultationController::class && $action->id === 'home') {
            if ($this->site && $this->site->getBehaviorClass()->siteHomeIsAlwaysPublic()) {
                return true;
            }
        }

        if ($this->testMaintenanceMode() || $this->testSiteForcedLogin() || $this->testConsultationPwd()) {
            return false;
        }
        return true;
    }

    /**
     * @param array|string $url
     * @param int $statusCode
     * @return mixed
     * @throws \yii\base\ExitException
     */
    public function redirect($url, $statusCode = 302)
    {
        $response = parent::redirect($url, $statusCode);
        Yii::$app->end();
        return $response;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isPostSet($name)
    {
        $post = Yii::$app->request->post();
        return isset($post[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isGetSet($name)
    {
        $get = Yii::$app->request->get();
        return isset($get[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRequestSet($name)
    {
        return $this->isPostSet($name) || $this->isGetSet($name);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getRequestValue($name, $default = null)
    {
        $post = Yii::$app->request->post();
        if (isset($post[$name])) {
            return $post[$name];
        }
        $get = Yii::$app->request->get();
        if (isset($get[$name])) {
            return $get[$name];
        }
        return $default;
    }

    /**
     * @param string $pageKey
     * @return string
     */
    public function renderContentPage($pageKey)
    {
        if ($this->consultation) {
            $admin = User::havePrivilege($this->consultation, User::PRIVILEGE_CONTENT_EDIT);
        } else {
            $user  = User::getCurrentUser();
            $admin = ($user && in_array($user->id, $this->getParams()->adminUserIds));
        }
        return $this->render(
            '@app/views/pages/contentpage',
            [
                'pageKey' => $pageKey,
                'admin'   => $admin,
            ]
        );
    }

    /**
     * @param string $view
     * @param array $options
     * @return string
     */
    public function render($view, $options = array())
    {
        $params = array_merge(
            [
                'consultation' => $this->consultation,
            ],
            $options
        );
        return parent::render($view, $params);
    }

    /**
     * @return AntragsgruenApp
     */
    public function getParams()
    {
        /** @var AntragsgruenApp $app */
        $app = Yii::$app->params;
        return $app;
    }

    /**
     * @param int|int[] $privilege
     * @return bool
     * @throws Internal
     */
    public function currentUserHasPrivilege($privilege)
    {
        if (!$this->consultation) {
            throw new Internal('No consultation set');
        }
        $user = User::getCurrentUser();
        if (!$user) {
            return false;
        }
        return $user->hasPrivilege($this->consultation, $privilege);
    }


    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function testMaintenanceMode()
    {
        if ($this->consultation == null) {
            return false;
        }
        /** @var \app\models\settings\Consultation $settings */
        $settings = $this->consultation->getSettings();
        $admin    = User::havePrivilege($this->consultation, User::PRIVILEGE_CONSULTATION_SETTINGS);
        if ($settings->maintenanceMode && !$admin) {
            $this->redirect(UrlHelper::createUrl(['pages/show-page', 'pageSlug' => 'maintenance']));
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function testSiteForcedLogin()
    {
        if ($this->consultation === null) {
            return false;
        }
        if (!$this->consultation->getSettings()->forceLogin) {
            return false;
        }
        if (Yii::$app->user->getIsGuest()) {
            $this->redirect(UrlHelper::createUrl(['user/login', 'backUrl' => $_SERVER['REQUEST_URI']]));
            return true;
        }
        if ($this->consultation->getSettings()->managedUserAccounts) {
            if (!User::havePrivilege($this->consultation, User::PRIVILEGE_ANY)) {
                $privilege = User::getCurrentUser()->getConsultationPrivilege($this->consultation);
                if (!$privilege || !$privilege->privilegeView) {
                    $this->redirect(UrlHelper::createUrl('user/consultationaccesserror'));
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function testConsultationPwd()
    {
        if (!Yii::$app->user->getIsGuest()) {
            return false;
        }
        if (!$this->consultation || !$this->consultation->getSettings()->accessPwd) {
            return false;
        }
        $pwdChecker = new ConsultationAccessPassword($this->consultation);
        if (!$pwdChecker->isCookieLoggedIn()) {
            $loginUrl = UrlHelper::createUrl([
                'user/login',
                'backUrl'   => Yii::$app->request->url,
                'passConId' => $this->consultation->urlPath,
            ]);
            $this->redirect($loginUrl);
            Yii::$app->end();
            return true;
        } else {
            return false;
        }
    }

    /**
     */
    public function forceLogin()
    {
        if (Yii::$app->user->getIsGuest()) {
            $loginUrl = UrlHelper::createUrl(['user/login', 'backUrl' => Yii::$app->request->url]);
            $this->redirect($loginUrl);
            Yii::$app->end();
        }
    }

    /**
     * @return string
     */
    public function showErrors()
    {
        $session = Yii::$app->session;
        if (!$session->isActive) {
            return '';
        }
        $str = '';

        $error = $session->getFlash('error', null, true);
        if ($error) {
            $str = '<div class="alert alert-danger" role="alert">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                <span class="sr-only">Error:</span>
                ' . nl2br(Html::encode($error)) . '
            </div>';
        }

        $success = $session->getFlash('success', null, true);
        if ($success) {
            $str .= '<div class="alert alert-success" role="alert">
                <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                <span class="sr-only">Success:</span>
                ' . Html::encode($success) . '
            </div>';
        }

        $info = $session->getFlash('info', null, true);
        if ($info) {
            $str .= '<div class="alert alert-info" role="alert">
                <span class="glyphicon glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                <span class="sr-only">Info:</span>
                ' . Html::encode($info) . '
            </div>';
        }

        $email = $session->getFlash('email', null, true);
        if ($email && YII_ENV == 'test') {
            $str .= '<div class="alert alert-info" role="alert">
                <span class="glyphicon glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                <span class="sr-only">Info:</span>
                ' . Html::encode($email) . '
            </div>';
        }

        return $str;
    }

    /**
     * @param $status
     * @param $message
     * @throws \yii\base\ExitException
     */
    protected function showErrorpage($status, $message)
    {
        $this->layoutParams->setFallbackLayoutIfNotInitializedYet();
        $this->layoutParams->robotsNoindex = true;
        Yii::$app->response->statusCode    = $status;
        Yii::$app->response->content       = $this->render(
            '@app/views/errors/error',
            [
                'httpStatus' => $status,
                'message'    => $message,
                'name'       => 'Error',
            ]
        );
        Yii::$app->end();
    }

    /**
     * @throws \yii\base\ExitException
     */
    protected function consultationNotFound()
    {
        $url     = Html::encode($this->getParams()->domainPlain);
        $message = str_replace('%URL%', $url, Yii::t('base', 'err_cons_404'));
        $this->showErrorpage(404, $message);
    }

    /**
     * @throws \yii\base\ExitException
     */
    protected function consultationError()
    {
        $this->showErrorpage(500, Yii::t('base', 'err_site_404'));
    }

    /**
     * @param null|Motion $checkMotion
     * @param null|Amendment $checkAmendment
     * @throws \yii\base\ExitException
     */
    protected function checkConsistency($checkMotion = null, $checkAmendment = null)
    {
        $consultationPath = strtolower($this->consultation->urlPath);
        $subdomain        = strtolower($this->site->subdomain);

        if (strtolower($this->consultation->site->subdomain) !== $subdomain) {
            Yii::$app->user->setFlash("error", Yii::t('base', 'err_cons_not_site'));
            $this->redirect(UrlHelper::createUrl('consultation/index'));
        }

        if (is_object($checkMotion) && strtolower($checkMotion->getMyConsultation()->urlPath) !== $consultationPath) {
            Yii::$app->session->setFlash('error', Yii::t('motion', 'err_not_found'));
            $this->redirect(UrlHelper::createUrl('consultation/index'));
        }

        if ($checkAmendment !== null && ($checkMotion === null || $checkAmendment->motionId !== $checkMotion->id)) {
            Yii::$app->session->setFlash('error', Yii::t('base', 'err_amend_not_consult'));
            $this->redirect(UrlHelper::createUrl('consultation/index'));
        }
    }

    /**
     * @param string $subdomain
     * @param string $consultationId
     * @param null|Motion $checkMotion
     * @param null|Amendment $checkAmendment
     * @return null|Consultation
     * @throws Internal
     * @throws \yii\base\ExitException
     */
    public function loadConsultation($subdomain, $consultationId = '', $checkMotion = null, $checkAmendment = null)
    {
        if (is_null($this->site)) {
            $this->site = Site::findOne(['subdomain' => $subdomain]);
        }
        if (is_null($this->site) || $this->site->status == Site::STATUS_DELETED || $this->site->dateDeletion !== null) {
            $this->consultationNotFound();
        }

        if ($consultationId == '') {
            $consultationId = $this->site->currentConsultation->urlPath;
        }

        if (is_null($this->consultation)) {
            $this->consultation = Consultation::findOne(['urlPath' => $consultationId, 'siteId' => $this->site->id]);
            if ($this->consultation && $this->consultation->getSettings()->getSpecializedLayoutClass()) {
                $layoutClass        = $this->consultation->getSettings()->getSpecializedLayoutClass();
                $this->layoutParams = new $layoutClass();
            }
        }
        if (is_null($this->consultation) || $this->consultation->dateDeletion !== null) {
            $this->consultationNotFound();
        } else {
            Consultation::setCurrent($this->consultation);
        }

        UrlHelper::setCurrentConsultation($this->consultation);
        UrlHelper::setCurrentSite($this->site);

        $this->layoutParams->setConsultation($this->consultation);

        $this->checkConsistency($checkMotion, $checkAmendment);

        return $this->consultation;
    }
}
