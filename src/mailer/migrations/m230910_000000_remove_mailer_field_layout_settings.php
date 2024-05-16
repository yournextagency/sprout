<?php

namespace BarrelStrength\Sprout\mailer\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\ProjectConfig;

class m230910_000000_remove_mailer_field_layout_settings extends Migration
{
    public const MAILERS_SETTINGS_KEY = 'sprout.sprout-module-mailer.mailers';

    public function safeUp(): void
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $mailers = $projectConfig->get(self::MAILERS_SETTINGS_KEY);

        if ($mailers === null) {
            return;
        }

        $mailersInProjectConfigHaveFieldLayouts = false;

        $mailerConfigs = ProjectConfig::unpackAssociativeArray($mailers);
        foreach ($mailerConfigs as $key => $mailerConfig) {
            if (isset($mailerConfigs[$key]['fieldLayouts'])) {
                $mailersInProjectConfigHaveFieldLayouts = true;
            }
            unset($mailerConfigs[$key]['fieldLayouts']);
        }

        // If mailers still have field layouts, remove them
        if ($mailersInProjectConfigHaveFieldLayouts) {
            $mailers = ProjectConfig::packAssociativeArray($mailerConfigs);
            $projectConfig->set(self::MAILERS_SETTINGS_KEY, $mailers);
        }
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
