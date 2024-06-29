<?php

namespace BarrelStrength\Sprout\mailer\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;

/**
 * @role temporary: Craft 4 -> 5
 * @schema sprout-module-mailer
 * @deprecated Remove in craftcms/cms:6.0
 */
class m240527_000000_content_refactor extends BaseContentRefactorMigration
{
    public const AUDIENCES_TABLE = '{{%sprout_audiences}}';

    public function safeUp(): void
    {
        // Update Audience Elements
        $this->updateElements(
            (new Query())->from(self::AUDIENCES_TABLE),
            null,
        );
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
