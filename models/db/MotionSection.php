<?php

namespace app\models\db;

use app\components\HTMLTools;
use app\components\LineSplitter;
use app\models\sectionTypes\ISectionType;
use app\models\exceptions\Internal;

/**
 * @package app\models\db
 *
 * @property int $motionId
 * @property int $sectionId
 * @property string $data
 * @property string $metadata
 *
 * @property Motion $motion
 * @property ConsultationSettingsMotionSection $consultationSetting
 * @property MotionComment[] $comments
 */
class MotionSection extends IMotionSection
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'motionSection';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConsultationSetting()
    {
        return $this->hasOne(ConsultationSettingsMotionSection::className(), ['id' => 'sectionId']);
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
    public function getComments()
    {
        return $this->hasMany(MotionComment::className(), ['motionId' => 'motionId', 'sectionId' => 'sectionId'])
            ->where('status != ' . IComment::STATUS_DELETED);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['motionId', 'sectionId'], 'required'],
            [['motionId', 'sectionId'], 'number'],
        ];
    }

    /**
     * @return \string[]
     * @throws Internal
     */
    public function getTextParagraphs()
    {
        if ($this->consultationSetting->type != ISectionType::TYPE_TEXT_SIMPLE) {
            throw new Internal('Paragraphs are only available for simple text sections.');
        }
        return HTMLTools::sectionSimpleHTML($this->data);
    }

    /**
     * @param string $para
     * @param bool $lineNumbers
     * @param int $lineLength
     * @return string[]
     */
    private function para2lines($para, $lineNumbers, $lineLength)
    {
        if (mb_stripos($para, '<ul>') === 0 || mb_stripos($para, '<ol>') === 0 ||
            mb_stripos($para, '<blockquote>') === 0
        ) {
            $lineLength -= 6;
        }
        $splitter = new LineSplitter($para, $lineLength);
        $linesIn  = $splitter->splitLines(false);

        if ($lineNumbers) {
            $linesOut = [];
            $pres     = ['<p>', '<ul><li>', '<ol><li>', '<blockquote><p>'];
            $linePre  = '###LINENUMBER###';
            foreach ($linesIn as $line) {
                $inserted = false;
                foreach ($pres as $pre) {
                    if (mb_stripos($line, $pre) === 0) {
                        $inserted = true;
                        $line     = str_ireplace($pre, $pre . $linePre, $line);
                    }
                }
                if (!$inserted) {
                    $line = $linePre . $line;
                }
                $linesOut[] = $line;
            }
        } else {
            $linesOut = $linesIn;
        }
        return $linesOut;
    }

    /**
     * @param bool $lineNumbers
     * @return MotionSectionParagraph[]
     * @throws Internal
     */
    public function getTextParagraphObjects($lineNumbers)
    {
        /** @var MotionSectionParagraph[] $return */
        $return = [];
        $paras  = $this->getTextParagraphs();
        foreach ($paras as $paraNo => $para) {
            $lineLength = $this->consultationSetting->consultation->getSettings()->lineLength;
            $linesOut   = $this->para2lines($para, $lineNumbers, $lineLength);

            $paragraph              = new MotionSectionParagraph();
            $paragraph->paragraphNo = $paraNo;
            $paragraph->lines       = $linesOut;
            $paragraph->amendments  = [];

            $paragraph->comments = [];
            foreach ($this->comments as $comment) {
                if ($comment->paragraph == $paraNo) {
                    $paragraph->comments[] = $comment;
                }
            }

            $return[] = $paragraph;
        }
        return $return;
    }

    /**
     * @return string
     * @throws Internal
     */
    public function getTextWithLineNumberPlaceholders()
    {
        $return = '';
        $paras  = $this->getTextParagraphs();
        foreach ($paras as $para) {
            $lineLength = $this->consultationSetting->consultation->getSettings()->lineLength;
            $linesOut   = $this->para2lines($para, true, $lineLength);
            $return .= implode(' ', $linesOut) . "\n";
        }
        return $return;
    }

    /**
     * @return int
     */
    public function getFirstLineNo()
    {
        return 1; // @TODO
    }
}
