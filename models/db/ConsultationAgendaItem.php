<?php

namespace app\models\db;

use app\components\MotionSorter;
use yii\db\ActiveRecord;

/**
 * Class ConsultationAgendaItem
 * @package app\models\db
 *
 * @property int $id
 * @property int $consultationId
 * @property int $parentItemId
 * @property int $position
 * @property string $code
 * @property string $title
 * @property string $description
 * @property int $motionTypeId
 * @property string $deadline
 *
 * @property Consultation $consultation
 * @property ConsultationAgendaItem $parentItem
 * @property ConsultationAgendaItem[] $childItems
 * @property ConsultationMotionType $motionType
 * @property Motion[] $motions
 */
class ConsultationAgendaItem extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        /** @var \app\models\settings\AntragsgruenApp $app */
        $app = \Yii::$app->params;
        return $app->tablePrefix . 'consultationAgendaItem';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConsultation()
    {
        return $this->hasOne(Consultation::class, ['id' => 'consultationId']);
    }

    /**
     * @return Consultation|null
     */
    public function getMyConsultation()
    {
        $consultation = Consultation::getCurrent();
        if ($consultation && $this->consultationId === $consultation->id) {
            return $consultation;
        } else {
            return $this->consultation;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentItem()
    {
        return $this->hasOne(ConsultationAgendaItem::class, ['id' => 'parentItemId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildItems()
    {
        return $this->hasMany(ConsultationAgendaItem::class, ['parentItemId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMotionType()
    {
        return $this->hasOne(ConsultationMotionType::class, ['id' => 'motionTypeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMotions()
    {
        return $this->hasMany(Motion::class, ['agendaItemId' => 'id'])
            ->andWhere(Motion::tableName() . '.status != ' . Motion::STATUS_DELETED);
    }

    /**
     * @return Motion[]
     */
    public function getMyMotions()
    {
        if ($this->getMyConsultation()->hasPreloadedMotionData()) {
            $motions = [];
            foreach ($this->getMyConsultation()->motions as $motion) {
                if ($motion->agendaItemId === $this->id) {
                    $motions[] = $motion;
                }
            }
            return $motions;
        } else {
            return $this->motions;
        }
    }

    /**
     * @return Motion[]
     */
    public function getMotionsFromConsultation()
    {
        $return = [];
        foreach ($this->getMyConsultation()->motions as $motion) {
            if (in_array($motion->status, $this->getMyConsultation()->getInvisibleMotionStatuses())) {
                continue;
            }
            if ($motion->agendaItemId === null || $motion->agendaItemId !== $this->id) {
                continue;
            }
            if (count($motion->getVisibleReplacedByMotions()) > 0 && $motion->status !== Motion::STATUS_MOVED) {
                // In case of "moved / copied", the whole point of copying it instead of just overwriting the old motion is so that it is still visible
                continue;
            }
            $return[] = $motion;
        }
        return $return;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['title', 'consultationId'], 'required'],
            [['title', 'code', 'description', 'deadline', 'position'], 'safe'],
            [['id', 'consultationId', 'parentItemId', 'position', 'motionTypeId'], 'number'],
        ];
    }

    /**
     * @param Consultation $consultation
     * @param int|null $parentItemId
     * @return ConsultationAgendaItem[]
     */
    public static function getItemsByParent(Consultation $consultation, $parentItemId)
    {
        $return = [];
        foreach ($consultation->agendaItems as $item) {
            if ($item->parentItemId === $parentItemId) {
                $return[] = $item;
            }
        }
        return $return;
    }

    /**
     * @param Consultation $consultation
     * @return ConsultationAgendaItem[]
     */
    public static function getSortedFromConsultation(Consultation $consultation)
    {
        $separator = \Yii::t('structure', 'top_separator');

        // Needs to be synchronized with antragsgruen.js:recalcAgendaCodes
        $calcNewShownCode = function ($currShownCode, $newInternalCode) use ($separator) {
            if ($newInternalCode === '#') {
                $currParts = explode($separator, $currShownCode);
                if (preg_match('/^[a-z]$/siu', $currParts[0])) { // Single alphabetical characters
                    $currParts[0] = chr(ord($currParts[0]) + 1);
                } else {  // Numbers or mixtures of alphabetical characters and numbers
                    preg_match('/^(?<non_numeric>.*[^0-9])?(?<numeric>[0-9]*)$/su', $currParts[0], $matches);
                    $nonNumeric   = $matches['non_numeric'];
                    $numeric      = ($matches['numeric'] === '' ? 1 : $matches['numeric']);
                    $currParts[0] = $nonNumeric . ++$numeric;
                }
                return implode($separator, $currParts);
            } else {
                return $newInternalCode;
            }
        };

        $getSubItems = function ($consultation, $item, $fullCodePrefix, $recFunc) use ($calcNewShownCode, $separator) {
            /** @var Consultation $consultation $items */
            /** @var ConsultationAgendaItem $item */
            if ($fullCodePrefix == '') {
                $fullCodePrefix = '0' . $separator;
            }
            $items         = [];
            $currShownCode = '0.';
            $children      = static::sortItems(static::getItemsByParent($consultation, $item->id));
            foreach ($children as $child) {
                $currShownCode = $calcNewShownCode($currShownCode, $child->code);
                $lastChar      = mb_substr($fullCodePrefix, mb_strlen($fullCodePrefix) - 1);
                $prevCode      = $fullCodePrefix . ($lastChar === $separator ? '' : $separator);
                $child->setShownCode($currShownCode, $prevCode . $currShownCode);
                $items = array_merge(
                    $items,
                    [$child],
                    $recFunc($consultation, $child, $prevCode . $currShownCode, $recFunc)
                );
            }
            return $items;
        };

        $items = [];
        $root  = [];
        foreach ($consultation->agendaItems as $item) {
            if ($item->parentItemId > 0) {
                continue;
            }
            $root[] = $item;
        }
        $root          = static::sortItems($root);
        $currShownCode = '0' . $separator;
        foreach ($root as $item) {
            $currShownCode = $calcNewShownCode($currShownCode, $item->code);
            $item->setShownCode($currShownCode, $currShownCode);
            $items = array_merge($items, [$item], $getSubItems($consultation, $item, $currShownCode, $getSubItems));
        }

        return $items;
    }

    /**
     * @param ConsultationAgendaItem[] $items
     * @return ConsultationAgendaItem[]
     */
    public static function sortItems($items)
    {
        usort(
            $items,
            function ($it1, $it2) {
                /** @var ConsultationAgendaItem $it1 */
                /** @var ConsultationAgendaItem $it2 */
                if ($it1->position < $it2->position) {
                    return -1;
                }
                if ($it1->position > $it2->position) {
                    return 1;
                }
                return 0;
            }
        );
        return $items;
    }

    /** @var string|null */
    private $shownCode     = null;
    private $shownCodeFull = null;

    /**
     * @param string $code
     * @param string $codeFull
     */
    protected function setShownCode($code, $codeFull)
    {
        $this->shownCode     = $code;
        $this->shownCodeFull = $codeFull;
    }

    /**
     * @param bool $full
     * @return string
     */
    public function getShownCode($full)
    {
        if ($this->shownCode === null) {
            $items = static::getSortedFromConsultation($this->getMyConsultation());
            foreach ($items as $item) {
                if ($item->id == $this->id) {
                    $this->shownCode     = $item->getShownCode(false);
                    $this->shownCodeFull = $item->getShownCode(true);
                }
            }
        }
        return ($full ? $this->shownCodeFull : $this->shownCode);
    }

    /**
     * @param bool $withdrawnAreVisible
     * @param bool $resolutionsAreVisible
     *
     * @return Motion[]
     */
    public function getVisibleMotions($withdrawnAreVisible = true, $resolutionsAreVisible = true)
    {
        $statuses = $this->getMyConsultation()->getInvisibleMotionStatuses($withdrawnAreVisible);
        if (!$resolutionsAreVisible) {
            $statuses[] = IMotion::STATUS_RESOLUTION_PRELIMINARY;
            $statuses[] = IMotion::STATUS_RESOLUTION_FINAL;
        }
        $return   = [];
        foreach ($this->getMyMotions() as $motion) {
            if (!in_array($motion->status, $statuses)) {
                $return[] = $motion;
            }
        }

        return $return;
    }

    /**
     * @param bool $withdrawnAreVisible
     *
     * @return Motion[]
     */
    public function getVisibleMotionsSorted($withdrawnAreVisible = true)
    {
        $motions = $this->getVisibleMotions($withdrawnAreVisible);
        return MotionSorter::getSortedMotionsFlat($this->getMyConsultation(), $motions);
    }
}
