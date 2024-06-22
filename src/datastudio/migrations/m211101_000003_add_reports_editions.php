<?php

namespace BarrelStrength\Sprout\datastudio\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\App;

class m211101_000003_add_reports_editions extends Migration
{
    public function safeUp(): void
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.sprout-reports.schemaVersion', true);
        $edition = $projectConfig->get('plugins.sprout-reports.edition', true);

        $proEditionHandle = App::editionHandle(Craft::Pro);

        // Reports only had one, commercial edition on c3 so if it's installed we upgrade it to the 'pro' edition
        if ($schemaVersion) {
            Craft::$app->getPlugins()->switchEdition('sprout-data-studio', $proEditionHandle);
        }
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
