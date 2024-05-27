<?php

namespace BarrelStrength\Sprout\datastudio\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

class m240527_000000_content_refactor extends BaseContentRefactorMigration
{
    public const DATASETS_TABLE = '{{%sprout_datasets}}';
    public const DATA_SET_ELEMENT_TYPE = 'BarrelStrength\Sprout\datastudio\components\elements\DataSetElement';

    public function safeUp(): void
    {
        $fieldLayouts = Craft::$app->projectConfig->get('sprout.sprout-module-data-studio.fieldLayouts');

        if ($fieldLayout = reset($fieldLayouts)) {
            $layout = FieldLayout::createFromConfig($fieldLayout);
        } else {
            $layout = new FieldLayout([
                'type' => self::DATA_SET_ELEMENT_TYPE,
            ]);
        }

        // Update Data Set Elements
        $this->updateElements(
            (new Query())->from(self::DATASETS_TABLE),
            $layout,
        );
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
