<?php

use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\Motion;
use app\models\db\MotionComment;
use app\models\db\MotionSupporter;
use app\models\db\User;
use app\models\forms\CommentForm;
use app\models\policies\IPolicy;
use app\models\policies\Nobody;
use app\views\motion\LayoutHelper;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var Motion $motion
 * @var int[] $openedComments
 * @var string|null $adminEdit
 * @var null|string $supportStatus
 * @var null|CommentForm $commentForm
 * @var bool $commentWholeMotions
 */

/** @var \app\controllers\Base $controller */
$controller = $this->context;
$layout     = $controller->layoutParams;
$layout->addAMDModule('frontend/MotionShow');
$layout->loadFuelux();

if ($controller->isRequestSet('backUrl') && $controller->isRequestSet('backTitle')) {
    $layout->addBreadcrumb($controller->getRequestValue('backTitle'), $controller->getRequestValue('backUrl'));
}
if (!$motion->getMyConsultation()->getForcedMotion()) {
    $layout->addBreadcrumb($motion->getBreadcrumbTitle());
}

if ($motion->isResolution()) {
    $this->title = $motion->getTitleWithIntro() . ' (' . $motion->getMyConsultation()->title . ')';
} else {
    $this->title = $motion->getTitleWithPrefix() . ' (' . $motion->getMyConsultation()->title . ')';
}

$sidebarRows = include(__DIR__ . DIRECTORY_SEPARATOR . '_view_sidebar.php');

$minimalisticUi          = $motion->getMyConsultation()->getSettings()->minimalisticUI;
$minHeight               = max($sidebarRows * 40 - 100, 0);
$supportCollectingStatus = ($motion->status === Motion::STATUS_COLLECTING_SUPPORTERS && !$motion->isDeadlineOver());

if ($motion->isResolution()) {
    echo '<h1>' . Html::encode($motion->getTitleWithIntro()) . '</h1>';
} else {
    echo '<h1>' . $motion->getEncodedTitleWithPrefix() . '</h1>';
}

echo $layout->getMiniMenu('motionSidebarSmall');

echo '<div class="motionData" style="min-height: ' . $minHeight . 'px;">';

if (!$minimalisticUi) {
    include(__DIR__ . DIRECTORY_SEPARATOR . '_view_motiondata.php');
}

echo $controller->showErrors();

if ($supportCollectingStatus) {
    echo '<div class="content" style="margin-top: 0;">';
    echo '<div class="alert alert-info supportCollectionHint" role="alert" style="margin-top: 0;">';
    $supportType  = $motion->motionType->getMotionSupportTypeClass();
    $min          = $supportType->getSettingsObj()->minSupporters;
    $curr         = count($motion->getSupporters());
    $missingFemale = $motion->getMissingSupporterCountByGender($supportType, 'female');
    if ($curr >= $min && !$missingFemale) {
        echo str_replace(['%MIN%', '%CURR%'], [$min, $curr], Yii::t('motion', 'support_collection_reached_hint'));
    } else {
        $minFemale = $supportType->getSettingsObj()->minSupportersFemale;
        if ($minFemale) {
            $currFemale = $motion->getSupporterCountByGender('female');
            echo str_replace(
                ['%MIN%', '%CURR%', '%MIN_F%', '%CURR_F%'],
                [$min, $curr, $minFemale, $currFemale],
                Yii::t('motion', 'support_collection_hint_female')
            );
        } else {
            echo str_replace(['%MIN%', '%CURR%'], [$min, $curr], Yii::t('motion', 'support_collection_hint'));
        }

        if ($motion->motionType->policySupportMotions !== IPolicy::POLICY_ALL && !User::getCurrentUser()) {
            $loginUrl = UrlHelper::createUrl(['user/login', 'backUrl' => Yii::$app->request->url]);
            echo '<div style="vertical-align: middle; line-height: 40px; margin-top: 20px;">';
            echo '<a href="' . Html::encode($loginUrl) . '" class="btn btn-default pull-right" rel="nofollow">' .
                 '<span class="icon glyphicon glyphicon-log-in" aria-hidden="true"></span> ' .
                 Yii::t('base', 'menu_login') . '</a>';

            echo Html::encode(Yii::t('structure', 'policy_logged_supp_denied'));
            echo '</div>';
        }
    }
    echo '</div></div>';
}
if ($motion->canFinishSupportCollection()) {
    echo Html::beginForm('', 'post', ['class' => 'motionSupportFinishForm']);

    echo '<button type="submit" name="motionSupportFinish" class="btn btn-success">';
    echo Yii::t('motion', 'support_finish_btn');
    echo '</button>';

    echo Html::endForm();
}


echo '</div>';

if (User::getCurrentUser() && !$motion->getPrivateComment(null, -1)) {
    ?>
    <div class="privateNoteOpener">
        <button class="btn btn-link btn-sm">
            <span class="glyphicon glyphicon-pushpin"></span>
            <?= Yii::t('motion', 'private_notes') ?>
        </button>
    </div>
    <?php
}

if ($motion->getMyMotionType()->getSettingsObj()->hasProposedProcedure) {
    if (!$motion->isResolution() && User::havePrivilege($consultation, User::PRIVILEGE_CHANGE_PROPOSALS)) {
        ?>
        <div class="proposedChangesOpener">
            <button class="btn btn-default btn-sm">
                <span class="glyphicon glyphicon-chevron-down"></span>
                <?= Yii::t('amend', 'proposal_open') ?>
            </button>
        </div>
        <?php

        echo $this->render('_set_proposed_procedure', ['motion' => $motion, 'msgAlert' => null]);
    }
    if ($motion->proposalFeedbackHasBeenRequested() && $motion->iAmInitiator()) {
        echo $this->render('_view_agree_to_proposal', ['motion' => $motion]);
    }
}

if ($motion->status === Motion::STATUS_DRAFT) {
    ?>
    <div class="content">
        <div class="alert alert-info alertDraft">
            <p><?= Yii::t('motion', 'info_draft_admin') ?></p>
        </div>
    </div>
    <?php
}


$viewText = $this->render('_view_text', [
    'motion'         => $motion,
    'commentForm'    => $commentForm,
    'openedComments' => $openedComments,
]);

$viewText = preg_replace_callback('/<!--PRIVATE_NOTE_(?<sectionId>\d+)_(?<paragraphNo>\d+)-->/siu', function ($matches) use ($motion) {
    return $this->render('_view_paragraph_private_note', [
        'motion'      => $motion,
        'sectionId'   => IntVal($matches['sectionId']),
        'paragraphNo' => IntVal($matches['paragraphNo']),
    ]);
}, $viewText);

echo $viewText;


?>
    <form class="gotoLineNumerPanel form-inline">
        <div class="form-group">
            <label class="sr-only" for="gotoLineNumber">Line number</label>
            <div class="input-group">
                <div class="input-group-addon"><?= Yii::t('motion', 'goto_line') ?>:</div>
                <input type="number" name="lineNumber" id="gotoLineNumber" class="form-control">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><?= Yii::t('motion', 'goto_line_go') ?></button>
                </span>
            </div>
        </div>

        <span class="lineNumberNotFound hidden"><?= Yii::t('motion', 'goto_line_err') ?></span>
    </form>
<?php

$currUserId    = (Yii::$app->user->isGuest ? 0 : Yii::$app->user->id);
$supporters    = $motion->getSupporters();
$supportType   = $motion->motionType->getMotionSupportTypeClass();
$supportPolicy = $motion->motionType->getMotionSupportPolicy();


if (count($supporters) > 0 || $supportCollectingStatus ||
    ($supportPolicy->checkCurrUser(false) && !$motion->isResolution())) {
    echo '<section class="supporters" id="supporters">
    <h2 class="green">' . Yii::t('motion', 'supporters_heading') . '</h2>
    <div class="content">';

    $iAmSupporting        = false;
    $anonymouslySupported = MotionSupporter::getMyAnonymousSupportIds();
    if (count($supporters) > 0) {
        echo '<ul>';
        foreach ($supporters as $supp) {
            /** @var MotionSupporter $supp */
            echo '<li>';
            if (($currUserId && $supp->userId === $currUserId) || in_array($supp->id, $anonymouslySupported)) {
                echo '<span class="label label-info">' . Yii::t('motion', 'supporting_you') . '</span> ';
                $iAmSupporting = true;
            }
            echo Html::encode($supp->getNameWithOrga());
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<em>' . Yii::t('motion', 'supporting_none') . '</em><br>';
    }
    echo '<br>';
    LayoutHelper::printSupportingSection($motion, $supportPolicy, $supportType, $iAmSupporting);
    echo '</div></section>';
}

if (!$motion->isResolution()) {
    LayoutHelper::printLikeDislikeSection($motion, $supportPolicy, $supportStatus);
}

echo \app\models\layoutHooks\Layout::afterMotionView($motion);

$amendments     = $motion->getVisibleAmendments();
$nobodyCanAmend = ($motion->motionType->getAmendmentPolicy()->getPolicyID() === IPolicy::POLICY_NOBODY);
if (count($amendments) > 0 || (!$nobodyCanAmend && !$motion->isResolution())) {
    echo '<section class="amendments"><h2 class="green">' . Yii::t('amend', 'amendments') . '</h2>
    <div class="content">';

    /** @noinspection PhpUnhandledExceptionInspection */
    if ($motion->isCurrentlyAmendable(false, true)) {
        echo '<div class="pull-right">';
        $title          = '<span class="icon glyphicon glyphicon-flash"></span>';
        $title          .= Yii::t('motion', 'amendment_create');
        $amendCreateUrl = UrlHelper::createUrl(['amendment/create', 'motionSlug' => $motion->getMotionSlug()]);
        echo '<a class="btn btn-default btn-sm" href="' . Html::encode($amendCreateUrl) . '" rel="nofollow">' .
             $title . '</a>';
        echo '</div>';
    }

    // Global alternatives first, then sorted by titlePrefix
    usort($amendments, function (Amendment $amend1, Amendment $amend2) {
        if ($amend1->globalAlternative && !$amend2->globalAlternative) {
            return -1;
        }
        if (!$amend1->globalAlternative && $amend2->globalAlternative) {
            return 1;
        }

        return strnatcasecmp($amend1->titlePrefix, $amend2->titlePrefix);
    });

    if (count($amendments) > 0) {
        echo '<ul class="amendments">';
        foreach ($amendments as $amend) {
            echo '<li>';
            if ($amend->globalAlternative) {
                echo '<strong>' . Yii::t('amend', 'global_alternative') . ':</strong> ';
            }
            $aename = $amend->titlePrefix;
            if ($aename === '') {
                $aename = $amend->id;
            }
            $amendLink     = UrlHelper::createAmendmentUrl($amend);
            $amendStatuses = Amendment::getStatusNames();
            echo Html::a(Html::encode($aename), $amendLink, ['class' => 'amendment' . $amend->id]);
            echo ' (' . Html::encode($amend->getInitiatorsStr() . ', ' . $amendStatuses[$amend->status]) . ')';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<em>' . Yii::t('motion', 'amends_none') . '</em>';
    }

    echo '</div></section>';
}

$nobodyCanComment = ($motion->motionType->getCommentPolicy()->getPolicyID() === Nobody::getPolicyID());
if ($commentWholeMotions && !$nobodyCanComment && !$motion->isResolution()) {
    echo '<section class="comments" data-antragsgruen-widget="frontend/Comments">';
    echo '<h2 class="green">' . Yii::t('motion', 'comments') . '</h2>';
    $form           = $commentForm;
    $screeningAdmin = User::havePrivilege($motion->getMyConsultation(), User::PRIVILEGE_SCREENING);

    $screening = Yii::$app->session->getFlash('screening', null, true);
    if ($screening) {
        echo '<div class="alert alert-success" role="alert">
                <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                <span class="sr-only">Success:</span>
                ' . Html::encode($screening) . '
            </div>';
    }

    if ($form === null || $form->paragraphNo != -1 || $form->sectionId != -1) {
        $form = new CommentForm($motion->getMyMotionType(), null);
        $form->setDefaultData(-1, -1, User::getCurrentUser());
    }

    $screeningQueue = 0;
    foreach ($motion->comments as $comment) {
        if ($comment->status === MotionComment::STATUS_SCREENING && $comment->paragraph === -1) {
            $screeningQueue++;
        }
    }
    if ($screeningQueue > 0) {
        echo '<div class="commentScreeningQueue">';
        if ($screeningQueue === 1) {
            echo Yii::t('motion', 'comment_screen_queue_1');
        } else {
            echo str_replace('%NUM%', $screeningQueue, Yii::t('motion', 'comment_screen_queue_x'));
        }
        echo '</div>';
    }

    foreach ($motion->getVisibleComments($screeningAdmin, -1, null) as $comment) {
        /** @var MotionComment $comment */
        echo $this->render('@app/views/motion/_comment', ['comment' => $comment]);
    }

    echo $form->renderFormOrErrorMessage();

    echo '</section>';
}
