<?php

/**
 * @var Yii\web\View $this
 * @var Consultation $consultation
 * @var string $locale
 */

use app\components\HTMLTools;
use app\components\UrlHelper;
use app\models\db\Consultation;
use app\models\db\Motion;
use yii\helpers\Html;

/** @var \app\controllers\admin\IndexController $controller */
$controller = $this->context;
$layout     = $controller->layoutParams;
/** @var \app\models\settings\AntragsgruenApp $params */
$params = Yii::$app->params;

$layout->addCSS('css/backend.css');
$layout->loadSortable();
$layout->loadFuelux();

$this->title = Yii::t('admin', 'con_h1');
$layout->addBreadcrumb(Yii::t('admin', 'bread_settings'), UrlHelper::createUrl('admin/index'));
$layout->addBreadcrumb(Yii::t('admin', 'bread_consultation'));

/**
 * @param \app\models\settings\Consultation $settings
 * @param string $field
 * @param array $handledSettings
 * @param string $description
 */
$boolSettingRow = function ($settings, $field, &$handledSettings, $description) {
    $handledSettings[] = $field;
    echo '<div><label>';
    echo Html::checkbox('settings[' . $field . ']', $settings->$field, ['id' => $field]) . ' ';
    echo $description;
    echo '</label></div>';
};

?><h1><?= Yii::t('admin', 'con_h1') ?></h1>
<?php
echo Html::beginForm('', 'post', [
    'id'                       => 'consultationSettingsForm',
    'class'                    => 'adminForm form-horizontal fuelux',
    'enctype'                  => 'multipart/form-data',
    'data-antragsgruen-widget' => 'backend/ConsultationSettings',
]);

echo $controller->showErrors();

$settings            = $consultation->getSettings();
$siteSettings        = $consultation->site->getSettings();
$handledSettings     = [];
$handledSiteSettings = [];

echo $consultation->site->getBehaviorClass()->getConsultationSettingsForm($consultation);

?>
    <h2 class="green"><?= Yii::t('admin', 'con_title_general') ?></h2>
    <div class="content">
        <div>
            <label>
                <?php
                $handledSettings[] = 'maintenanceMode';
                echo Html::checkbox(
                    'settings[maintenanceMode]',
                    $settings->maintenanceMode,
                    ['id' => 'maintenanceMode']
                );
                ?>
                <strong><?= Yii::t('admin', 'con_maintenance') ?></strong>
                <small><?= Yii::t('admin', 'con_maintenance_hint') ?></small>
            </label>
        </div>


        <div class="form-group">
            <label class="col-sm-3 control-label" for="consultationPath"><?= Yii::t('admin', 'con_url_path') ?>
                :</label>
            <div class="col-sm-9 urlPathHolder">
                <div class="shower">
                    <?= Html::encode($consultation->urlPath) ?>
                    [<a href="#"><?= Yii::t('admin', 'con_url_change') ?></a>]
                </div>
                <div class="holder hidden">
                    <input type="text" required name="consultation[urlPath]"
                           value="<?= Html::encode($consultation->urlPath) ?>" class="form-control"
                           pattern="[\w_-]+" id="consultationPath">
                    <small><?= Yii::t('admin', 'con_url_path_hint') ?></small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label" for="consultationTitle"><?= Yii::t('admin', 'con_title') ?>:</label>
            <div class="col-sm-9">
                <input type="text" required name="consultation[title]" value="<?= Html::encode($consultation->title) ?>"
                       class="form-control" id="consultationTitle">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label"
                   for="consultationTitleShort"><?= Yii::t('admin', 'con_title_short') ?>:</label>
            <div class="col-sm-9">
                <input type="text" required name="consultation[titleShort]"
                       maxlength="<?= Consultation::TITLE_SHORT_MAX_LEN ?>"
                       value="<?= Html::encode($consultation->titleShort) ?>"
                       class="form-control" id="consultationTitleShort">
            </div>
        </div>

        <?php $handledSettings[] = 'lineLength'; ?>
        <fieldset class="form-group">
            <label class="col-sm-3 control-label" for="lineLength"><?= Yii::t('admin', 'con_line_len') ?>
                :</label>
            <div class="col-sm-3">
                <input type="number" required name="settings[lineLength]"
                       value="<?= Html::encode($settings->lineLength) ?>" class="form-control" id="lineLength">
            </div>
        </fieldset>

        <?php $handledSettings[] = 'robotsPolicy'; ?>
        <fieldset class="form-group">
            <div class="col-sm-3 control-label">
                <?= Yii::t('admin', 'con_robots') ?>:
                <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" title=""
                      data-original-title="<?= Html::encode(Yii::t('admin', 'con_robots_hint')) ?>"></span>
            </div>
            <div class="col-sm-9">
                <?php
                foreach (\app\models\settings\Consultation::getRobotPolicies() as $policy => $policyName) {
                    echo '<label>';
                    echo Html::radio('settings[robotsPolicy]', ($settings->robotsPolicy == $policy), [
                        'value' => $policy,
                    ]);
                    echo ' ' . Html::encode($policyName) . '</label><br>';
                }
                ?>
            </div>
        </fieldset>

    </div>
    <h2 class="green"><?= Yii::t('admin', 'con_appearance') ?></h2>
    <div class="content">


        <?php $handledSettings[] = 'startLayoutType'; ?>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="startLayoutType">
                <?= Yii::t('admin', 'con_home_page_style') ?>:
            </label>
            <div class="col-sm-9"><?php
                echo HTMLTools::fueluxSelectbox(
                    'settings[startLayoutType]',
                    $consultation->getSettings()->getStartLayouts(),
                    $consultation->getSettings()->startLayoutType,
                    ['id' => 'startLayoutType'],
                    true
                );
                ?></div>
        </div>

        <fieldset class="form-group">
            <div class="col-sm-3 control-label"><?= Yii::t('admin', 'con_ci') ?>:</div>
            <div class="col-sm-9 thumbnailedLayoutSelector">
                <?php
                $layoutId              = $consultation->site->getSettings()->siteLayout;
                $handledSiteSettings[] = 'siteLayout';
                foreach (\app\models\settings\Layout::getCssLayouts($this) as $lId => $cssLayout) {
                    echo '<label class="layout ' . $lId . '">';
                    echo Html::radio('siteSettings[siteLayout]', $lId === $layoutId, ['value' => $lId]);
                    echo '<span><img src="' . Html::encode($cssLayout['preview']) . '" ' .
                         'alt="' . Html::encode($cssLayout['title']) . '" ' .
                         'title="' . Html::encode($cssLayout['title']) . '"></span>';
                    echo '</label>';
                }
                ?>
            </div>
            <div class="col-sm-3"></div>
            <div class="col-sm-9 customThemeSelector">
                <label>
                    <?php
                    $isCustom  = (strpos($layoutId, 'layout-custom-') !== false);
                    $hasCustom = (count($consultation->site->getSettings()->stylesheetSettings) > 0);
                    $options   = ['value' => $layoutId];
                    if (!$hasCustom) {
                        $options['disabled'] = 'disabled';
                    }
                    echo Html::radio('siteSettings[siteLayout]', $isCustom, $options);
                    echo ' ' . Yii::t('admin', 'con_ci_custom');
                    ?>
                </label>
                <?= Html::a(
                    '<span class="glyphicon glyphicon-chevron-right"></span> ' .
                    ($hasCustom ? Yii::t('admin', 'con_ci_custom_edit') : Yii::t('admin', 'con_ci_custom_create')),
                    UrlHelper::createUrl(['/admin/index/theming', 'default' => 'DEFAULT']),
                    ['class' => 'editThemeLink']
                ) ?>
            </div>
        </fieldset>

        <fieldset class="form-group logoRow">
            <label class="col-sm-3 control-label" for="logoUrl"><?= Yii::t('admin', 'con_logo_url') ?>:</label>
            <div class="col-sm-2 logoPreview">
                <?php
                if ($settings->logoUrl) {
                    echo $layout->getLogoStr();
                }
                ?>
            </div>
            <div class="col-sm-7 imageChooser">
                <input type="hidden" name="consultationLogo" value="" autocomplete="off">
                <div class="uploadCol">
                    <input type="file" name="newLogo" class="form-control" id="logoUrl">
                    <label for="logoUrl">
                        <span class="glyphicon glyphicon-upload"></span>
                        <span class="text" data-title="<?= Html::encode(Yii::t('admin', 'con_logo_url_upload')) ?>">
                            <?= Yii::t('admin', 'con_logo_url_upload') ?>
                        </span>
                    </label>
                </div>
                <?php
                $images = $consultation->site->getFileImages();
                if (count($images) > 0) {
                    $imgEditLink = UrlHelper::createUrl('/admin/index/files');
                    ?>
                    <div class="dropdown imageChooserDd">
                        <button class="btn btn-default dropdown-toggle" type="button" id="fileChooseDropdownBtn"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <?= Yii::t('admin', 'con_logo_url_choose') ?>
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="fileChooseDropdownBtn">
                            <ul>
                                <?php
                                foreach ($images as $file) {
                                    $src = $file->getUrl();
                                    echo '<li><a href="#"><img alt="" src="' . Html::encode($src) . '"></a></li>';
                                }
                                ?>
                            </ul>
                            <a href="<?= Html::encode($imgEditLink) ?>" class="imageEditLink pull-right">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                                <?= Html::encode(Yii::t('admin', 'con_logo_edit_images')) ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </fieldset>

        <?php

        $boolSettingRow($settings, 'hideTitlePrefix', $handledSettings, Yii::t('admin', 'con_prefix_hide'));

        $handledSiteSettings[] = 'showAntragsgruenAd';
        echo '<div><label>';
        echo Html::checkbox('siteSettings[showAntragsgruenAd]', $siteSettings->showAntragsgruenAd, ['id' => 'showAntragsgruenAd']) . ' ';
        echo Yii::t('admin', 'con_show_ad');
        echo '</label></div>';

        $propTitle = Yii::t('admin', 'con_proposal_procedure');
        $tooltip   = ' <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" ' .
                     'title="" data-original-title="' . Html::encode(Yii::t('admin', 'con_proposal_tt')) . '"></span>';
        $boolSettingRow($settings, 'proposalProcedurePage', $handledSettings, $propTitle . $tooltip);

        $boolSettingRow($settings, 'minimalisticUI', $handledSettings, Yii::t('admin', 'con_minimalistic'));

        ?>
    </div>

    <h2 class="green"><?= Yii::t('admin', 'con_title_motions') ?></h2>
    <div class="content">

        <div><label>
                <?php
                echo Html::checkbox(
                    'settings[singleMotionMode]',
                    ($settings->forceMotion !== null),
                    ['id' => 'singleMotionMode']
                );
                echo ' ' . Yii::t('admin', 'con_single_motion_mode');
                ?>
            </label></div>


        <?php
        $handledSettings[] = 'forceMotion';
        $motions           = [];
        foreach ($consultation->motions as $motion) {
            if ($motion->status !== Motion::STATUS_DELETED) {
                $motions[$motion->id] = $motion->getTitleWithPrefix();
            }
        }
        ?>
        <div class="form-group" id="forceMotionRow">
            <label class="col-sm-3 control-label" for="startLayoutType"><?= Yii::t('admin', 'con_force_motion') ?>
                :</label>
            <div class="col-sm-9"><?php
                echo HTMLTools::fueluxSelectbox(
                    'settings[forceMotion]',
                    $motions,
                    $settings->forceMotion,
                    ['id' => 'forceMotion'],
                    true
                );
                ?></div>
        </div>


        <div><label>
                <?php
                $handledSettings[] = 'lineNumberingGlobal';
                echo Html::checkbox(
                    'settings[lineNumberingGlobal]',
                    $settings->lineNumberingGlobal,
                    ['id' => 'lineNumberingGlobal']
                );
                echo ' ' . Yii::t('admin', 'con_line_number_global');
                ?>
            </label></div>


        <div><label>
                <?php
                $handledSettings[] = 'screeningMotions';
                echo Html::checkbox(
                    'settings[screeningMotions]',
                    $settings->screeningMotions,
                    ['id' => 'screeningMotions']
                );
                echo ' ' . Yii::t('admin', 'con_motion_screening');
                ?>
            </label></div>

        <?php
        $boolSettingRow($settings, 'adminsMayEdit', $handledSettings, Yii::t('admin', 'con_admins_may_edit'));

        $boolSettingRow($settings, 'odtExportHasLineNumers', $handledSettings, Yii::t('admin', 'con_odt_has_lines'));

        $boolSettingRow($settings, 'iniatorsMayEdit', $handledSettings, Yii::t('admin', 'con_initiators_may_edit'));

        $boolSettingRow($settings, 'screeningMotionsShown', $handledSettings, Yii::t('admin', 'con_show_screening'));


        $tags = $consultation->getSortedTags();
        ?>
        <div class="form-group">
            <div class="col-sm-3 control-label"><?= Yii::t('admin', 'con_topics') ?>:</div>
            <div class="col-sm-9">

                <div class="pillbox" data-initialize="pillbox" id="tagsList">
                    <ul class="clearfix pill-group" id="tagsListUl">
                        <?php
                        foreach ($tags as $tag) {
                            echo '<li class="btn btn-default pill" data-id="' . $tag->id . '">
        <span>' . Html::encode($tag->title) . '</span>
        <span class="glyphicon glyphicon-close"><span class="sr-only">Remove</span></span>
    </li>';
                        }
                        ?>
                        <li class="pillbox-input-wrap btn-group">
                            <a class="pillbox-more">and <span class="pillbox-more-count"></span> more...</a>
                            <input type="text" class="form-control dropdown-toggle pillbox-add-item"
                                   placeholder="<?= Yii::t('admin', 'con_topic_add') ?>">
                            <button type="button" class="dropdown-toggle sr-only">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <label>
                    <?php
                    $handledSettings[] = 'allowMultipleTags';
                    echo Html::checkbox(
                        'settings[allowMultipleTags]',
                        $settings->allowMultipleTags,
                        ['id' => 'allowMultipleTags']
                    );
                    echo ' ' . Yii::t('admin', 'con_multiple_topics');
                    ?>
                </label>
            </div>
        </div>

        <?php
        $organisations = $consultation->getSettings()->organisations;
        if ($organisations === null) {
            $organisations = [];
        }
        ?>
        <div class="form-group">
            <div class="col-sm-3 control-label">
                <?= Yii::t('admin', 'con_organisations') ?>:
                <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" title=""
                      data-original-title="<?= Html::encode(Yii::t('admin', 'con_organisations_hint')) ?>"></span>
            </div>
            <div class="col-sm-9">
                <div class="pillbox" data-initialize="pillbox" id="organisationList">
                    <ul class="clearfix pill-group" id="organisationListUl">
                        <?php
                        foreach ($organisations as $organisation) {
                            echo '<li class="btn btn-default pill">
        <span>' . Html::encode($organisation) . '</span>
        <span class="glyphicon glyphicon-close"><span class="sr-only">Remove</span></span>
    </li>';
                        }
                        ?>
                        <li class="pillbox-input-wrap btn-group">
                            <a class="pillbox-more">and <span class="pillbox-more-count"></span> more...</a>
                            <input type="text" class="form-control dropdown-toggle pillbox-add-item"
                                   placeholder="<?= Yii::t('admin', 'con_organisation_add') ?>">
                            <button type="button" class="dropdown-toggle sr-only">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h2 class="green"><?= Yii::t('admin', 'con_title_amendments') ?></h2>
    <div class="content">

        <div class="form-group">
            <label class="col-sm-3 control-label" for="amendmentNumbering">
                <?= Yii::t('admin', 'con_amend_number') ?>:
            </label>
            <div class="col-sm-9"><?php
                echo HTMLTools::fueluxSelectbox(
                    'consultation[amendmentNumbering]',
                    \app\models\amendmentNumbering\IAmendmentNumbering::getNames(),
                    $consultation->amendmentNumbering,
                    ['id' => 'amendmentNumbering'],
                    true
                );
                ?></div>
        </div>


        <div><label>
                <?php
                $handledSettings[] = 'screeningAmendments';
                echo Html::checkbox(
                    'settings[screeningAmendments]',
                    $settings->screeningAmendments,
                    ['id' => 'screeningAmendments']
                );
                echo ' ' . Yii::t('admin', 'con_amend_screening');
                ?>
            </label></div>

        <div><label>
                <?php
                $handledSettings[] = 'editorialAmendments';
                echo Html::checkbox(
                    'settings[editorialAmendments]',
                    $settings->editorialAmendments,
                    ['id' => 'editorialAmendments']
                );
                echo ' ' . Yii::t('admin', 'con_amend_editorial');
                ?>
            </label></div>

        <div><label>
                <?php
                $handledSettings[] = 'globalAlternatives';
                echo Html::checkbox(
                    'settings[globalAlternatives]',
                    $settings->globalAlternatives,
                    ['id' => 'globalAlternatives']
                );
                echo ' ' . Yii::t('admin', 'con_amend_globalalt');
                ?>
            </label></div>
    </div>

    <h2 class="green"><?= Yii::t('admin', 'con_title_comments') ?></h2>

    <div class="content">

        <div><label>
                <?php
                $handledSettings[] = 'screeningComments';
                echo Html::checkbox(
                    'settings[screeningComments]',
                    $settings->screeningComments,
                    ['id' => 'screeningComments']
                );
                echo ' ' . Yii::t('admin', 'con_comment_screening');
                ?>
            </label></div>

        <div><label>
                <?php
                $handledSettings[] = 'commentNeedsEmail';
                echo Html::checkbox(
                    'settings[commentNeedsEmail]',
                    $settings->commentNeedsEmail,
                    ['id' => 'commentNeedsEmail']
                );
                echo ' ' . Yii::t('admin', 'con_comment_email');
                ?>
            </label></div>

    </div>
    <h2 class="green"><?= Yii::t('admin', 'con_title_email') ?></h2>
    <div class="content">
        <div class="form-group">
            <label class="col-sm-3 control-label" for="adminEmail"><?= Yii::t('admin', 'con_email_admins') ?>:</label>
            <div class="col-sm-9">
                <input type="text" name="consultation[adminEmail]"
                       value="<?= Html::encode($consultation->adminEmail) ?>"
                       class="form-control" id="adminEmail">
            </div>
        </div>

        <?php
        $handledSettings[] = 'emailFromName';
        $placeholder       = str_replace('%NAME%', $params->mailFromName, Yii::t('admin', 'con_email_from_place'));
        ?>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="emailFromName">
                <?= Yii::t('admin', 'con_email_from') ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" name="settings[emailFromName]" class="form-control" id="emailFromName"
                       placeholder="<?= Html::encode($placeholder) ?>"
                       value="<?= Html::encode($settings->emailFromName) ?>">
            </div>
        </div>

        <?php $handledSettings[] = 'emailReplyTo'; ?>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="emailReplyTo">Reply-To:</label>
            <div class="col-sm-9">
                <input type="email" name="settings[emailReplyTo]" class="form-control" id="emailReplyTo"
                       placeholder="<?= Yii::t('admin', 'con_email_replyto_place') ?>"
                       value="<?= Html::encode($settings->emailReplyTo) ?>">
            </div>
        </div>

        <div>
            <label>
                <?php
                $handledSettings[] = 'initiatorConfirmEmails';
                echo Html::checkbox(
                    'settings[initiatorConfirmEmails]',
                    $settings->initiatorConfirmEmails,
                    ['id' => 'initiatorConfirmEmails']
                );
                echo ' ' . Yii::t('admin', 'con_send_motion_email');
                ?>
            </label>
        </div>


        <div class="saveholder">
            <button type="submit" name="save" class="btn btn-primary"><?= Yii::t('admin', 'save') ?></button>
        </div>


    </div>

<?php
foreach ($handledSettings as $setting) {
    echo '<input type="hidden" name="settingsFields[]" value="' . Html::encode($setting) . '">';
}
foreach ($handledSiteSettings as $setting) {
    echo '<input type="hidden" name="siteSettingsFields[]" value="' . Html::encode($setting) . '">';
}
echo Html::endForm();
