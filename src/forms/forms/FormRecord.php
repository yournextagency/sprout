<?php

namespace BarrelStrength\Sprout\forms\forms;

use BarrelStrength\Sprout\forms\db\SproutTable;
use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * @property int $id
 * @property string $submissionFieldLayoutUid
 * @property string $name
 * @property string $handle
 * @property string $titleFormat
 * @property string $formTypeUid
 * @property array $formTypeSettings
 */
class FormRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return SproutTable::FORMS;
    }

    public function getElement(): ActiveQueryInterface
    {
        return self::hasOne(Element::class, ['id' => 'id']);
    }
}
