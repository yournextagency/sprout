<?php

namespace BarrelStrength\Sprout\transactional\migrations;

use BarrelStrength\Sprout\mailer\emailtypes\EmailTypeHelper;
use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

class m240527_000000_content_refactor extends BaseContentRefactorMigration
{
    public const EMAILS_TABLE = '{{%sprout_emails}}';

    public function safeUp(): void
    {
        $emailTypes = EmailTypeHelper::getEmailTypes();

        // Update Email Elements
        foreach ($emailTypes as $emailType) {
            $this->updateElements(
                (new Query())
                    ->from(self::EMAILS_TABLE)
                    ->where(['emailTypeUid' => $emailType->uid]),
                $emailType->getFieldLayout(),
            );
        }

    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
