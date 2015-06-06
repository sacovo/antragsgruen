<?php

namespace app\models\db;

use app\components\RSSExporter;
use app\components\Tools;
use app\components\UrlHelper;

/**
 * @package app\models\db
 *
 * @property int $id
 * @property int $userId
 * @property int $motionId
 * @property int $sectionId
 * @property int $paragraph
 * @property string $text
 * @property string $name
 * @property string $contactEmail
 * @property string $dateCreation
 * @property int $status
 * @property int $replyNotification
 *
 * @property User $user
 * @property Motion $motion
 * @property MotionCommentSupporter[] $supporters
 * @property MotionSection $section
 */
class MotionComment extends IComment
{
    const STATUS_VISIBLE   = 0;
    const STATUS_DELETED   = -1;
    const STATUS_SCREENING = 1;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'motionComment';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMotion()
    {
        return $this->hasOne(Motion::className(), ['id' => 'motionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupporters()
    {
        return $this->hasMany(MotionCommentSupporter::className(), ['motionCommentId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSection()
    {
        return $this->hasOne(MotionSection::className(), ['motionId' => 'motionId', 'sectionId' => 'sectionId']);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['motionId', 'sectionId', 'paragraph', 'status', 'dateCreation'], 'required'],
            ['name', 'required', 'message' => 'Bitte gib deinen Namen an.'],
            ['text', 'required', 'message' => 'Bitte gib etwas Text ein.'],
            [['id', 'motionId', 'sectionId', 'paragraph', 'status'], 'number'],
            [['text', 'paragraph'], 'safe'],
        ];
    }

    /**
     * @param Consultation $consultation
     * @param int $limit
     * @return MotionComment[]
     */
    public static function getNewestByConsultation(Consultation $consultation, $limit = 5)
    {
        $invisibleStati = array_map('IntVal', $consultation->getInvisibleMotionStati());

        return static::find()->joinWith('motion', true)
            ->where('motionComment.status = ' . IntVal(static::STATUS_VISIBLE))
            ->andWhere('motion.status NOT IN (' . implode(', ', $invisibleStati) . ')')
            ->andWhere('motion.consultationId = ' . IntVal($consultation->id))
            ->orderBy('motionComment.dateCreation DESC')
            ->offset(0)->limit($limit)->all();
    }

    /**
     * @return Consultation
     */
    public function getConsultation()
    {
        return $this->motion->consultation;
    }

    /**
     * @return string
     */
    public function getMotionTitle()
    {
        return $this->motion->getTitleWithPrefix();
    }

    /**
     * @param RSSExporter $feed
     */
    public function addToFeed(RSSExporter $feed)
    {
        $feed->addEntry(
            UrlHelper::createMotionCommentUrl($this),
            $this->getMotionTitle(),
            $this->name,
            $this->text,
            Tools::dateSql2timestamp($this->dateCreation)
        );
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->dateCreation;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return UrlHelper::createMotionCommentUrl($this);
    }
}
