<?php

namespace BarrelStrength\Sprout\redirects\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

/**
 * @role temporary: Craft 4 => 5
 * @schema sprout-module-redirects
 * @deprecated Remove in craftcms/cms:6.0
 */
class m240526_000000_content_refactor extends BaseContentRefactorMigration
{
    public const REDIRECTS_TABLE = '{{%sprout_redirects}}';
    public const REDIRECT_ELEMENT_TYPE = 'BarrelStrength\Sprout\redirects\components\elements\RedirectElement';

    public function safeUp(): void
    {
        $fieldLayouts = Craft::$app->getProjectConfig()->get('sprout.sprout-module-redirects.fieldLayouts');

        if ($fieldLayout = reset($fieldLayouts)) {
            $layout = FieldLayout::createFromConfig($fieldLayout);
        } else {
            $layout = new FieldLayout([
                'type' => self::REDIRECT_ELEMENT_TYPE,
            ]);
        }

        // Update Redirect Elements
        $this->updateElements(
            (new Query())->from(self::REDIRECTS_TABLE),
            $layout,
        );
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
