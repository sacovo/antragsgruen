<?php

use app\components\AntiSpam;
use app\components\HTMLTools;
use app\models\db\Consultation;
use app\models\db\IComment;
use app\models\db\User;
use app\models\db\UserNotification;
use app\models\forms\CommentForm;
use Yii\Helpers\Html;

/**
 * @var int $paragraphNo
 * @var int $sectionId
 * @var CommentForm $form
 * @var Consultation $consultation
 * @var IComment $isReplyTo
 */

$user = User::getCurrentUser();
if ($user) {
    $notiSettings = UserNotification::getNotification($user, $consultation, UserNotification::NOTIFICATION_NEW_COMMENT);
} else {
    $notiSettings = null;
}

$settingOptions = [
    UserNotification::COMMENT_REPLIES             => Yii::t('con', 'noti_comments_replies'),
    UserNotification::COMMENT_SAME_MOTIONS        => Yii::t('con', 'noti_comments_motions'),
    UserNotification::COMMENT_ALL_IN_CONSULTATION => Yii::t('con', 'noti_comments_con'),
];
$setting        = UserNotification::$COMMENT_SETTINGS[0];
if ($notiSettings) {
    $setting = $notiSettings->getSettingByKey('comments', UserNotification::$COMMENT_SETTINGS[0]);
}

$classes = 'commentForm motionComment form-horizontal';
if ($isReplyTo) {
    $classes   .= ' replyComment replyTo' . $isReplyTo->id . ' hidden';
    $title     = Yii::t('comment', 'comment_reply_title');
    $formIdPre = 'comment_' . $sectionId . '_' . $paragraphNo . '_' . $isReplyTo->id;
} else {
    $title     = Yii::t('comment', 'comment_write_title');
    $formIdPre = 'comment_' . $sectionId . '_' . $paragraphNo;
}
echo Html::beginForm('', 'post', ['class' => $classes, 'id' => $formIdPre . '_form']);

if ($user && $user->name) {
    echo '<div class="commentName">' . Html::encode($form->name) . ' (' . Html::encode($form->email) . ')</div>';
}
echo '<h3 class="commentHeader commentWriteHeader">' . $title . '</h3>';

if (Yii::$app->user->isGuest) {
    echo AntiSpam::getJsProtectionHint($consultation->id);
}

?>
    <input type="hidden" name="comment[paragraphNo]" value="<?= $paragraphNo ?>">
    <input type="hidden" name="comment[sectionId]" value="<?= $sectionId ?>">
<?php
if ($isReplyTo) {
    echo '<input type="hidden" name="comment[parentCommentId]" value="' . $isReplyTo->id . '">';
}

if ($user && $user->name && $user->email) {
    ?>
    <div class="commentFullTextarea">
        <textarea name="comment[text]" title="<?= Html::encode(Yii::t('comment', 'text')) ?>" class="form-control"
                  rows="5" id="<?= $formIdPre ?>_text"><?= Html::encode($form->text) ?></textarea>
    </div>
    <?php
} else {
    if (!$user || !$user->name) {
        ?>
        <div class="form-group">
            <label for="<?= $formIdPre ?>_name" class="control-label col-sm-3">
                <?= Yii::t('comment', 'name') ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control col-sm-9" id="<?= $formIdPre ?>_name"
                       name="comment[name]" value="<?= Html::encode($form->name) ?>" required autocomplete="name">
            </div>
        </div>
        <?php
    }
    if (!$user || !$user->email) {
        ?>
        <div class="form-group">
            <label for="<?= $formIdPre ?>_email" class="control-label col-sm-3">
                <?= Yii::t('comment', 'email') ?>:
            </label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="<?= $formIdPre ?>_email"
                       autocomplete="email" name="comment[email]"
                       value="<?= Html::encode($form->email) ?>"
                    <?= ($consultation->getSettings()->commentNeedsEmail ? ' required' : '') ?>>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="form-group">
        <label for="<?= $formIdPre ?>_text" class="control-label col-sm-3"><?= Yii::t('comment', 'text') ?>
            :</label>
        <div class="col-sm-9">
            <textarea name="comment[text]" title="Text" class="form-control" rows="5"
                      id="<?= $formIdPre ?>_text"><?= Html::encode($form->text) ?></textarea>
        </div>
    </div>
    <?php
}

if ($user) {
    ?>
    <div class="commentNotifications">
        <label>
            <?= Html::checkbox('comment[notifications]', ($notiSettings !== null), ['class' => 'notisActive']) ?>
            <?= Yii::t('comment', 'set_notis') ?>
        </label>
        <?= HTMLTools::fueluxSelectbox('comment[notificationsettings]', $settingOptions, $setting, [], false, 'xs') ?>
    </div>
    <?php
}
?>

    <div class="submitrow">
        <button class="btn btn-success" name="writeComment" type="submit">
            <?= Yii::t('comment', 'submit_comment') ?>
        </button>
    </div>
<?php
echo Html::endForm();
