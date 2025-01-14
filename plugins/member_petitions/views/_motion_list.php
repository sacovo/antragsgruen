<?php

use app\components\UrlHelper;
use app\models\db\IMotion;
use app\models\db\Motion;
use app\plugins\member_petitions\Tools;
use yii\helpers\Html;

/**
 * @var Motion[] $motions
 * @var string $bold
 * @var bool $statusClustering
 */

if (count($motions) === 0) {
    echo Yii::t('member_petitions', 'none');
    return;
}

if ($statusClustering) {
    $lastPhase = 0;

    usort($motions, function (IMotion $motion1, IMotion $motion2) {
        $phase1 = Tools::getMotionPhaseNumber($motion1);
        $phase2 = Tools::getMotionPhaseNumber($motion2);
        if ($phase1 < $phase2) {
            return -1;
        } elseif ($phase1 > $phase2) {
            return 1;
        } else {
            $created1 = Tools::getMotionTimestamp($motion1);
            $created2 = Tools::getMotionTimestamp($motion2);
            if ($created1 < $created2) {
                return 1;
            } elseif ($created1 > $created2) {
                return -1;
            } else {
                return 0;
            }
        }
    });
}

echo '<ul class="motionList motionListPetitions">';
foreach ($motions as $motion) {
    $status = $motion->getFormattedStatus();

    $motionPhase     = Tools::getMotionPhaseNumber($motion);
    $motionPhaseName = null;
    switch ($motionPhase) {
        case 1:
            $motionPhaseName = Yii::t('member_petitions', 'status_discussing');
            break;
        case 2:
            $motionPhaseName = Yii::t('member_petitions', 'status_collecting');
            break;
        case 3:
            $motionPhaseName = Yii::t('member_petitions', 'status_unanswered');
            break;
        case 4:
            $motionPhaseName = Yii::t('member_petitions', 'status_answered');
            break;
    }

    if ($statusClustering) {
        if ($motionPhase !== $lastPhase) {
            $classes = 'sortitem green phase' . $motionPhase;
            echo '<li class="' . $classes . '" data-phase="' . $motionPhase . '" data-created="' . $motionPhase . '">' .
                $motionPhaseName . '</li>';
            $lastPhase = $motionPhase;
        }
    }

    $cssClasses   = ['sortitem', 'motion'];
    $cssClasses[] = 'motionRow' . $motion->id;
    $cssClasses[] = 'phase' . $motionPhase;
    foreach ($motion->tags as $tag) {
        $cssClasses[] = 'tag' . $tag->id;
    }

    $commentCount   = $motion->getNumOfAllVisibleComments(false);
    $amendmentCount = count($motion->getVisibleAmendments(false));
    $publication    = $motion->datePublication;

    echo '<li class="' . implode(' ', $cssClasses) . '" ' .
        'data-phase="' . $motionPhase . '"' .
        'data-created="' . Tools::getMotionTimestamp($motion) . '" ' .
        'data-num-comments="' . $commentCount . '" ' .
        'data-num-amendments="' . $amendmentCount . '">';
    echo '<p class="stats">';

    if ($amendmentCount > 0) {
        echo '<span class="amendments"><span class="glyphicon glyphicon-flash"></span> ' . $amendmentCount . '</span>';
    }
    if ($commentCount > 0) {
        echo '<span class="comments"><span class="glyphicon glyphicon-comment"></span> ' . $commentCount . '</span>';
    }
    echo '</p>' . "\n";
    echo '<p class="title">' . "\n";

    $motionUrl = UrlHelper::createMotionUrl($motion);
    echo '<a href="' . Html::encode($motionUrl) . '" class="motionLink' . $motion->id . '">';

    $title = ($motion->title === '' ? '-' : $motion->title);
    echo ' <span class="motionTitle">' . Html::encode($title) . '</span>';

    echo '</a>';
    echo "</p>\n";
    echo '<p class="info">';
    if ($bold === 'organization') {
        echo '<span class="status">' . Html::encode($motion->getMyConsultation()->title) . '</span>, ';
    }
    echo Html::encode($motion->getInitiatorsStr()) . ', ';
    echo \app\components\Tools::formatMysqlDate($motion->dateCreation);

    if ($bold !== 'organization') {
        echo '<span class="phaseName">. ' . $motionPhaseName;
        if ($motion->status === IMotion::STATUS_COLLECTING_SUPPORTERS) {
            $max = $motion->getMyMotionType()->getMotionSupportTypeClass()->getSettingsObj()->minSupporters;
            $curr = count($motion->getSupporters());
            echo ' (' . $curr . ' / ' . $max . ')';
        }
        echo '</span>';
    }

    $deadline = Tools::getPetitionResponseDeadline($motion);
    if ($deadline) {
        echo ', ' . Yii::t('member_petitions', 'index_remaining') . ': ';
        echo \app\components\Tools::formatRemainingTime($deadline);
    }

    $deadline = Tools::getDiscussionUntil($motion);
    if ($deadline) {
        echo ', ' . Yii::t('member_petitions', 'index_remaining') . ': ';
        echo \app\components\Tools::formatRemainingTime($deadline);
    }

    if ($motion->status === Motion::STATUS_PAUSED) {
        echo '<span class="timeOver">' . Yii::t('member_petitions', 'status_paused') . '</span>';
    }
    echo '</p>';
    $abstract = null;
    foreach ($motion->getSortedSections(true) as $section) {
        if ($section->getSettings()->type === \app\models\sectionTypes\ISectionType::TYPE_TITLE) {
            $abstract = $section->data;
        }
    }
    if ($abstract) {
        echo '<blockquote class="abstract">' . Html::encode($abstract) . '</blockquote>';
    }
    echo '</li>';
}
echo '</ul>';
