<?php

namespace BarrelStrength\Sprout\datastudio\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

/**
 * @role temporary: Craft 4 => 5
 * @schema sprout-module-data-studio
 * @deprecated Remove in craftcms/cms:6.0
 */
class m240527_000000_content_refactor extends BaseContentRefactorMigration
{
    public const DATASETS_TABLE = '{{%sprout_datasets}}';
    public const DATA_SET_ELEMENT_TYPE = 'BarrelStrength\Sprout\datastudio\components\elements\DataSetElement';

    public function safeUp(): void
    {
        $fieldLayouts = Craft::$app->getProjectConfig()->get('sprout.sprout-module-data-studio.fieldLayouts');

        if (is_array($fieldLayouts) && $fieldLayout = reset($fieldLayouts)) {
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
