<?php

namespace BarrelStrength\Sprout\datastudio\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\App;

class m240516_000000_remove_reports_plugin_from_project_config extends Migration
{
    public function safeUp(): void
    {
        // Don't make the same config changes twice
        $reportPluginConfig = Craft::$app->getProjectConfig()->get('plugins.sprout-reports', true);

        if ($reportPluginConfig) {
            Craft::$app->getProjectConfig()->remove('plugins.sprout-reports');
        }
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
