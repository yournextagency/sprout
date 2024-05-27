<?php

namespace BarrelStrength\Sprout\sentemail\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

class m240527_000000_content_refactor extends BaseContentRefactorMigration
{
    public const SENT_EMAILS_TABLE = '{{%sprout_sent_emails}}';

    public function safeUp(): void
    {
        // Update Sent Email Elements
        $this->updateElements(
            (new Query())->from(self::SENT_EMAILS_TABLE),
            null
        );
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
