<?php

use app\components\HTMLTools;
use app\components\Tools;
use app\components\UrlHelper;
use app\models\db\ConsultationAgendaItem;
use app\models\db\Motion;
use app\models\db\MotionSupporter;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var Motion $motion
 * @var \app\models\forms\MotionEditForm $form
 */

/** @var \app\controllers\Base $controller */
$controller   = $this->context;
$layout       = $controller->layoutParams;
$consultation = $controller->consultation;

$this->title = Yii::t('admin', 'motion_edit_title') . ': ' . $motion->getTitleWithPrefix();
$layout->addBreadcrumb(Yii::t('admin', 'bread_list'), UrlHelper::createUrl('admin/motion-list/index'));
$layout->addBreadcrumb(Yii::t('admin', 'bread_motion'));

$layout->addCSS('css/backend.css');
$layout->loadSortable();
$layout->loadDatepicker();
$layout->loadCKEditor();
$layout->loadFuelux();
$layout->addJS('npm/clipboard.min.js');

$html = '<ul class="sidebarActions">';
$html .= '<li><a href="' . Html::encode(UrlHelper::createMotionUrl($motion)) . '" class="view">';
$html .= '<span class="icon glyphicon glyphicon-file"></span>' . Yii::t('admin', 'motion_show') . '</a></li>';

$cloneUrl = UrlHelper::createUrl(['motion/create', 'cloneFrom' => $motion->id]);
$html     .= '<li><a href="' . Html::encode($cloneUrl) . '" class="clone">';
$html     .= '<span class="icon glyphicon glyphicon-duplicate"></span>' .
             Yii::t('admin', 'motion_new_base_on_this') . '</a></li>';

$moveUrl = UrlHelper::createUrl(['admin/motion/move', 'motionId' => $motion->id]);
$html     .= '<li><a href="' . Html::encode($moveUrl) . '" class="move">';
$html     .= '<span class="icon glyphicon glyphicon-arrow-right"></span>' .
             Yii::t('admin', 'motion_move') . '</a></li>';

$html .= '<li>' . Html::beginForm('', 'post', ['class' => 'motionDeleteForm']);
$html .= '<input type="hidden" name="delete" value="1">';
$html .= '<button type="submit" class="link"><span class="icon glyphicon glyphicon-trash"></span>'
         . Yii::t('admin', 'motion_del') . '</button>';
$html .= Html::endForm() . '</li>';

$html                .= '</ul>';
$layout->menusHtml[] = $html;


echo '<h1>' . $motion->getEncodedTitleWithPrefix() . '</h1>';

echo $controller->showErrors();


if ($motion->isInScreeningProcess()) {
    echo Html::beginForm('', 'post', ['class' => 'content', 'id' => 'motionScreenForm']);
    $newRev = $motion->titlePrefix;
    if ($newRev === '') {
        $newRev = $motion->getMyConsultation()->getNextMotionPrefix($motion->motionTypeId);
    }

    echo '<input type="hidden" name="titlePrefix" value="' . Html::encode($newRev) . '">';

    echo '<div style="text-align: center;"><button type="submit" class="btn btn-primary" name="screen">';
    echo Html::encode(str_replace('%PREFIX%', $newRev, Yii::t('admin', 'motion_screen_as_x')));
    echo '</button></div>';

    echo Html::endForm();

    echo "<br>";
}


echo Html::beginForm('', 'post', [
    'id'                       => 'motionUpdateForm',
    'enctype'                  => 'multipart/form-data',
    'data-antragsgruen-widget' => 'backend/MotionEdit',
]);

echo '<div class="content form-horizontal fuelux">';

?>

    <div class="form-group">
        <label class="col-md-3 control-label" for="motionType"><?= Yii::t('admin', 'motion_type') ?></label>
        <div class="col-md-9">
            <?php
            $options = [];
            foreach ($motion->motionType->getCompatibleMotionTypes() as $motionType) {
                $options[$motionType->id] = $motionType->titleSingular;
            }
            $attrs = ['id' => 'motionType'];
            echo HTMLTools::fueluxSelectbox('motion[motionType]', $options, $motion->motionTypeId, $attrs, true);
            ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="parentMotion"><?= Yii::t('admin', 'motion_replaces') ?></label>
        <div class="col-md-9">
            <?php
            $options = ['-'];
            foreach ($consultation->motions as $otherMotion) {
                $options[$otherMotion->id] = $otherMotion->getTitleWithPrefix();
            }
            $attrs = ['id' => 'parentMotion'];
            echo HTMLTools::fueluxSelectbox('motion[parentMotionId]', $options, $motion->parentMotionId, $attrs, true);
            ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="motionStatus"><?= Yii::t('admin', 'motion_status') ?>:</label>
        <div class="col-md-5">
            <?php
            $stats = Motion::getStatusNamesVisibleForAdmins();
            echo HTMLTools::fueluxSelectbox('motion[status]', $stats, $motion->status, ['id' => 'motionStatus'], true);
            echo '</div><div class="col-md-4">';
            $options = ['class' => 'form-control', 'id' => 'motionStatusString', 'placeholder' => '...'];
            echo Html::textInput('motion[statusString]', $motion->statusString, $options);
            ?>
        </div>
    </div>

<?php
if (count($consultation->agendaItems) > 0) {
    echo '<div class="form-group">';
    echo '<label class="col-md-3 control-label" for="agendaItemId">';
    echo Yii::t('admin', 'motion_agenda_item');
    echo ':</label><div class="col-md-9">';
    $options    = ['id' => 'agendaItemId'];
    $selections = [];
    foreach (ConsultationAgendaItem::getSortedFromConsultation($consultation) as $item) {
        $selections[$item->id] = $item->title;
    }

    echo HTMLTools::fueluxSelectbox('motion[agendaItemId]', $selections, $motion->agendaItemId, $options, true);
    echo '</div></div>';
}
?>

    <div class="form-group">
        <label class="col-md-3 control-label" for="motionTitle"><?= Yii::t('admin', 'motion_title') ?>:</label>
        <div class="col-md-9">
            <?php
            $placeholder = Yii::t('admin', 'motion_title');
            $options     = ['class' => 'form-control', 'id' => 'motionTitle', 'placeholder' => $placeholder];
            echo Html::textInput('motion[title]', $motion->title, $options);
            ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="motionTitlePrefix"><?= Yii::t('admin', 'motion_prefix') ?>:</label>
        <div class="col-md-4"><?php
            echo Html::textInput('motion[titlePrefix]', $motion->titlePrefix, [
                'class'       => 'form-control',
                'id'          => 'motionTitlePrefix',
                'placeholder' => Yii::t('admin', 'motion_prefix_hint')
            ]);
            ?>
            <small><?= Yii::t('admin', 'motion_prefix_unique') ?></small>
        </div>
    </div>

<?php
$locale = Tools::getCurrentDateLocale();
$date   = Tools::dateSql2bootstraptime($motion->dateCreation);
?>
    <div class="form-group">
        <label class="col-md-3 control-label" for="motionDateCreation">
            <?= Yii::t('admin', 'motion_date_created') ?>:
        </label>
        <div class="col-md-4">
            <div class="input-group date" id="motionDateCreationHolder">
                <input type="text" class="form-control" name="motion[dateCreation]" id="motionDateCreation"
                       value="<?= Html::encode($date) ?>" data-locale="<?= Html::encode($locale) ?>">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
    </div>

<?php
$date = Tools::dateSql2bootstraptime($motion->datePublication);
?>
    <div class="form-group">
        <label class="col-md-3 control-label" for="motionDatePublication">
            <?= Yii::t('admin', 'motion_date_publication') ?>:
        </label>
        <div class="col-md-4">
            <div class="input-group date" id="motionDatePublicationHolder">
                <input type="text" class="form-control" name="motion[datePublication]" id="motionDatePublication"
                       value="<?= Html::encode($date) ?>" data-locale="<?= Html::encode($locale) ?>">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
    </div>

<?php
$date = Tools::dateSql2bootstraptime($motion->dateResolution);
?>
    <div class="form-group">
        <label class="col-md-3 control-label" for="motionDateResolution">
            <?= Yii::t('admin', 'motion_date_resolution') ?>:
        </label>
        <div class="col-md-4">
            <div class="input-group date" id="motionDateResolutionHolder">
                <input type="text" class="form-control" name="motion[dateResolution]" id="motionDateResolution"
                       value="<?= Html::encode($date) ?>" data-locale="<?= Html::encode($locale) ?>">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
    </div>

<?php if (count($consultation->tags) > 0) { ?>
    <div class="form-group">
        <label class="col-md-3 control-label">
            <?= Yii::t('admin', 'motion_topics') ?>:
        </label>
        <div class="col-md-9 tagList">
            <?php
            foreach ($consultation->tags as $tag) {
                echo '<label><input type="checkbox" name="tags[]" value="' . $tag->id . '"';
                foreach ($motion->tags as $mtag) {
                    if ($mtag->id == $tag->id) {
                        echo ' checked';
                    }
                }
                echo '> ' . Html::encode($tag->title) . '</label>';
            }
            ?>
        </div>
    </div>
<?php } ?>

    <div class="form-group">
        <label class="col-md-3 control-label" for="nonAmendable">
            <?= Yii::t('admin', 'motion_non_amendable_title') ?>:
        </label>
        <div class="col-md-9 nonAmendable">
            <label>
                <input type="checkbox" name="motion[nonAmendable]" value="1" id="nonAmendable"
                    <?= ($motion->nonAmendable ? 'checked' : '') ?>>
                <?= Yii::t('admin', 'motion_non_amendable') ?>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-3 control-label" for="motionNoteInternal">
            <?= Yii::t('admin', 'motion_note_internal') ?>:
        </label>
        <div class="col-md-9">
            <?php
            $options = ['class' => 'form-control', 'id' => 'motionNoteInternal'];
            echo Html::textarea('motion[noteInternal]', $motion->noteInternal, $options);
            ?>
        </div>
    </div>

<?php
$voting       = $motion->getVotingData();
$votingOpened = $voting->hasAnyData();
?>
    <div class="contentVotingResultCaller">
        <button class="btn btn-link votingResultOpener <?= ($votingOpened ? 'hidden' : '') ?>" type="button">
            <span class="glyphicon glyphicon-chevron-down"></span>
            <?= Yii::t('amend', 'merge_new_votes_enter') ?>
        </button>
        <button class="btn btn-link votingResultCloser <?= ($votingOpened ? '' : 'hidden') ?>" type="button">
            <span class="glyphicon glyphicon-chevron-up"></span>
            <?= Yii::t('amend', 'merge_new_votes_enter') ?>:
        </button>
    </div>
    <div class="form-group contentVotingResultComment <?= ($votingOpened ? '' : 'hidden') ?>">
        <label class="col-md-3 control-label" for="votesComment">
            <?= Yii::t('amend', 'merge_new_votes_comment') ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" name="votes[comment]" type="text" id="votesComment"
                   value="<?= Html::encode($voting->comment ? $voting->comment : '') ?>">
        </div>
    </div>
    <div class="contentVotingResult row <?= ($votingOpened ? '' : 'hidden') ?>">
        <div class="col-md-3">
            <label for="votesYes"><?= Yii::t('amend', 'merge_new_votes_yes') ?></label>
            <input class="form-control" name="votes[yes]" type="number" id="votesYes"
                   value="<?= Html::encode($voting->votesYes ? $voting->votesYes : '') ?>">
        </div>
        <div class="col-md-3">
            <label for="votesNo"><?= Yii::t('amend', 'merge_new_votes_no') ?></label>
            <input class="form-control" name="votes[no]" type="number" id="votesNo"
                   value="<?= Html::encode($voting->votesNo ? $voting->votesNo : '') ?>">
        </div>
        <div class="col-md-3">
            <label for="votesAbstention"><?= Yii::t('amend', 'merge_new_votes_abstention') ?></label>
            <input class="form-control" name="votes[abstention]" type="number" id="votesAbstention"
                   value="<?= Html::encode($voting->votesAbstention ? $voting->votesAbstention : '') ?>">
        </div>
        <div class="col-md-3">
            <label for="votesInvalid"><?= Yii::t('amend', 'merge_new_votes_invalid') ?></label>
            <input class="form-control" name="votes[invalid]" type="number" id="votesInvalid"
                   value="<?= Html::encode($voting->votesInvalid ? $voting->votesInvalid : '') ?>">
        </div>
    </div>

<?php
echo '</div>';


$needsCollisionCheck = (!$motion->textFixed && count($motion->getAmendmentsRelevantForCollisionDetection()) > 0);
if (!$motion->textFixed) {
    echo '<h2 class="green">' . Yii::t('admin', 'motion_edit_text') . '</h2>
<div class="content" id="motionTextEditCaller">' .
         Yii::t('admin', 'motion_edit_text_warn') . '
    <br><br>
    <button type="button" class="btn btn-default">' . Yii::t('admin', 'motion_edit_btn') . '</button>
</div>
<div class="content hidden" id="motionTextEditHolder">';

    if ($needsCollisionCheck) {
        echo '<div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            <span class="sr-only">' . Yii::t('admin', 'motion_amrew_warning') . ':</span> ' .
             Yii::t('admin', 'motion_amrew_intro') .
             '</div>';
    }

    foreach ($form->sections as $section) {
        if ($motion->getTitleSection() && $section->sectionId === $motion->getTitleSection()->sectionId) {
            continue;
        }
        echo $section->getSectionType()->getMotionFormField();
    }

    $url = UrlHelper::createUrl(['admin/motion/get-amendment-rewrite-collisions', 'motionId' => $motion->id]);
    echo '<section class="amendmentCollisionsHolder"></section>';
    if ($needsCollisionCheck) {
        echo '<div class="checkButtonRow">';
        echo '<button class="checkAmendmentCollisions btn btn-default" data-url="' . Html::encode($url) . '">' .
             Yii::t('admin', 'motion_amrew_btn1') . '</button>';
        echo '</div>';
    }
    echo '</div>';
}

$initiatorClass = $form->motionType->getMotionSupportTypeClass();
$initiatorClass->setAdminMode(true);
echo $initiatorClass->getMotionForm($form->motionType, $form, $controller);

echo $this->render('_update_supporter', [
    'supporters'  => $motion->getSupporters(),
    'newTemplate' => new MotionSupporter(),
    'settings'    => $initiatorClass->getSettingsObj(),
]);

echo '<div class="saveholder">';
if ($needsCollisionCheck) {
    $url = UrlHelper::createUrl(['admin/motion/get-amendment-rewrite-collisions', 'motionId' => $motion->id]);
    echo '<button class="checkAmendmentCollisions btn btn-default" data-url="' . Html::encode($url) . '">' .
         Yii::t('admin', 'motion_amrew_btn2') . '</button>';
}
echo '<button type="submit" name="save" class="btn btn-primary save">' . Yii::t('admin', 'save') . '</button>
</div>';

echo Html::endForm();
