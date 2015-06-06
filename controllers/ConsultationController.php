<?php

namespace app\controllers;

use app\components\MessageSource;
use app\components\RSSExporter;
use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\ConsultationAgendaItem;
use app\models\db\IComment;
use app\models\db\Motion;
use app\models\db\Consultation;
use app\models\db\MotionComment;
use app\models\db\User;
use app\models\exceptions\Access;
use app\models\exceptions\FormError;

class ConsultationController extends Base
{
    /**
     *
     */
    public function actionSearch()
    {
        // @TODO
    }


    /**
     *
     */
    public function actionFeedmotions()
    {
        $this->testMaintainanceMode();
        $newest = Motion::getNewestByConsultation($this->consultation, 20);

        $feed = new RSSExporter();
        if ($this->consultation->getSettings()->logoUrl != '') {
            $feed->setImage($this->consultation->getSettings()->logoUrl);
        } else {
            $feed->setImage('/img/logo.png');
        }
        $feed->setTitle($this->consultation->title . ': ' . 'Anträge');
        $feed->setLanguage(\yii::$app->language);
        $feed->setBaseLink(UrlHelper::createUrl('consultation/index'));
        $feed->setFeedLink(UrlHelper::createUrl('consultation/feedmotions'));
        foreach ($newest as $motion) {
            $motion->addToFeed($feed);
        }

        \yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        \yii::$app->response->headers->add('Content-Type', 'application/xml');
        return $feed->getFeed();
    }

    /**
     *
     */
    public function actionFeedamendments()
    {
        $this->testMaintainanceMode();
        $newest = Amendment::getNewestByConsultation($this->consultation, 20);

        $feed = new RSSExporter();
        if ($this->consultation->getSettings()->logoUrl != '') {
            $feed->setImage($this->consultation->getSettings()->logoUrl);
        } else {
            $feed->setImage('/img/logo.png');
        }
        $feed->setTitle($this->consultation->title . ': ' . 'Änderungsanträge');
        $feed->setLanguage(\yii::$app->language);
        $feed->setBaseLink(UrlHelper::createUrl('consultation/index'));
        $feed->setFeedLink(UrlHelper::createUrl('consultation/feedamendments'));
        foreach ($newest as $amend) {
            $amend->addToFeed($feed);
        }

        \yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        \yii::$app->response->headers->add('Content-Type', 'application/xml');
        return $feed->getFeed();
    }

    /**
     *
     */
    public function actionFeedcomments()
    {
        $this->testMaintainanceMode();
        $newest = IComment::getNewestByConsultation($this->consultation, 20);

        $feed = new RSSExporter();
        if ($this->consultation->getSettings()->logoUrl != '') {
            $feed->setImage($this->consultation->getSettings()->logoUrl);
        } else {
            $feed->setImage('/img/logo.png');
        }
        $feed->setTitle($this->consultation->title . ': ' . 'Kommentare');
        $feed->setLanguage(\yii::$app->language);
        $feed->setBaseLink(UrlHelper::createUrl('consultation/index'));
        $feed->setFeedLink(UrlHelper::createUrl('consultation/feedcomments'));
        foreach ($newest as $comm) {
            $comm->addToFeed($feed);
        }

        \yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        \yii::$app->response->headers->add('Content-Type', 'application/xml');
        return $feed->getFeed();
    }

    /**
     *
     */
    public function actionFeedall()
    {
        // @TODO
    }

    /**
     *
     */
    public function actionPdfs()
    {
        // @TODO
    }

    /**
     *
     */
    public function actionAmendmentpdfs()
    {
        // @TODO
    }

    /**
     *
     */
    public function actionNotifications()
    {
        // @TODO
    }

    /**
     * @param string $pageKey
     * @return string
     * @throws Access
     */
    public function actionSavetextajax($pageKey)
    {
        if (!User::currentUserHasPrivilege($this->consultation, User::PRIVILEGE_CONTENT_EDIT)) {
            throw new Access('No permissions to edit this page');
        }
        if (MessageSource::savePageData($this->consultation, $pageKey, $_POST['data'])) {
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * @return string
     */
    public function actionMaintainance()
    {
        return $this->renderContentPage('maintainance');
    }

    /**
     * @return string
     */
    public function actionLegal()
    {
        return $this->renderContentPage('legal');
    }

    /**
     * @return string
     */
    public function actionPrivacy()
    {
        return $this->renderContentPage('privacy');
    }

    /**
     * @return string
     */
    public function actionHelp()
    {
        return $this->renderContentPage('help');
    }

    /**
     * @param Consultation $consultation
     */
    private function consultationSidebar(Consultation $consultation)
    {
        $newestAmendments = Amendment::getNewestByConsultation($consultation, 5);
        $newestMotions    = Motion::getNewestByConsultation($consultation, 3);
        $newestComments   = IComment::getNewestByConsultation($consultation, 3);

        $this->renderPartial(
            'sidebar',
            [
                'newestMotions'    => $newestMotions,
                'newestAmendments' => $newestAmendments,
                'newestComments'   => $newestComments,
            ]
        );
    }


    /**
     * @param array $arr
     * @param int|null $parentId
     * @return \int[]
     * @throws FormError
     */
    private function saveAgendaArr($arr, $parentId)
    {
        $items = [];
        foreach ($arr as $i => $jsitem) {
            if ($jsitem['id'] > 0) {
                $consultationId = IntVal($this->consultation->id);
                $condition      = ['id' => IntVal($jsitem['id']), 'consultationId' => $consultationId];
                /** @var ConsultationAgendaItem $item */
                $item = ConsultationAgendaItem::findOne($condition);
                if (!$item) {
                    throw new FormError('Inconsistency - did not find given agenda item: ' . $condition);
                }
            } else {
                $item                 = new ConsultationAgendaItem();
                $item->consultationId = $this->consultation->id;
            }

            $item->code         = $jsitem['code'];
            $item->title        = $jsitem['title'];
            $item->motionTypeId = ($jsitem['motionTypeId'] > 0 ? $jsitem['motionTypeId'] : null);
            $item->parentItemId = $parentId;
            $item->position     = $i;

            $item->save();
            $items[] = $item->id;

            $items = array_merge($items, $this->saveAgendaArr($jsitem['children'], $item->id));
        }
        return $items;
    }

    /**
     * @throws FormError
     */
    private function saveAgenda()
    {
        if (!User::currentUserHasPrivilege($this->consultation, User::PRIVILEGE_CONTENT_EDIT)) {
            \Yii::$app->session->setFlash('error', 'No permissions to edit this page');
            return;
        }

        $data = json_decode($_POST['data'], true);
        if (!is_array($data)) {
            \Yii::$app->session->setFlash('error', 'Could not parse input');
            return;
        }

        try {
            $usedItems = $this->saveAgendaArr($data, null);
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return;
        }

        foreach ($this->consultation->agendaItems as $item) {
            if (!in_array($item->id, $usedItems)) {
                $item->delete();
            }
        }

        $this->consultation->refresh();

        \Yii::$app->session->setFlash('success', 'Saved');
    }


    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'column2';

        $this->testMaintainanceMode();
        $this->consultationSidebar($this->consultation);

        if (isset($_POST['saveAgenda'])) {
            $this->saveAgenda();
        }


        $myself = User::getCurrentUser();
        if ($myself) {
            $myMotions    = $myself->getMySupportedMotionsByConsultation($this->consultation);
            $myAmendments = $myself->getMySupportedAmendmentsByConsultation($this->consultation);
        } else {
            $myMotions    = null;
            $myAmendments = null;
        }

        $saveUrl = UrlHelper::createUrl(['consultation/savetextajax', 'pageKey' => 'welcome']);

        return $this->render(
            'index',
            array(
                'consultation' => $this->consultation,
                'myself'       => $myself,
                'myMotions'    => $myMotions,
                'myAmendments' => $myAmendments,
                'admin'        => User::currentUserHasPrivilege($this->consultation, User::PRIVILEGE_CONTENT_EDIT),
                'saveUrl'      => $saveUrl,
            )
        );
    }
}
