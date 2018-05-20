<?php
namespace app\models\db;

use yii\db\ActiveRecord;

/**
 * @package app\models\db
 *
 * @property int $id
 * @property int $consultationId
 * @property int $siteId
 * @property string $category
 * @property string $textId
 * @property string $text
 * @property string $editDate
 *
 * @property Consultation $consultation
 * @property Site $site
 */
class ConsultationText extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        /** @var \app\models\settings\AntragsgruenApp $app */
        $app = \Yii::$app->params;
        return $app->tablePrefix . 'consultationText';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConsultation()
    {
        return $this->hasOne(Consultation::class, ['id' => 'consultationId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['category', 'textId'], 'required'],
            [['category', 'textId', 'text'], 'safe'],
        ];
    }
}
