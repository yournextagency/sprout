<?php

/** @noinspection DuplicatedCode */

namespace BarrelStrength\Sprout\redirects\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m211101_000001_migrate_settings_table_to_projectconfig extends Migration
{
    public const SPROUT_KEY = 'sprout';
    public const MODULES_KEY = self::SPROUT_KEY . '.sprout-module-redirects.modules';
    public const MODULE_ID = 'sprout-module-redirects';
    public const MODULE_CLASS = 'BarrelStrength\Sprout\redirects\RedirectsModule';
    public const OLD_SETTINGS_MODEL = 'barrelstrength\sproutbaseredirects\models\Settings';
    public const OLD_SETTINGS_TABLE = '{{%sprout_settings_craft3}}';

    public function safeUp(): void
    {
        $moduleSettingsKey = self::SPROUT_KEY . '.' . self::MODULE_ID;
        $coreModuleSettingsKey = self::MODULES_KEY . '.' . self::MODULE_CLASS;

        // Table renamed first in core migrations
        if (!$this->db->tableExists(self::OLD_SETTINGS_TABLE)) {
            return;
        }

        $oldSettings = (new Query())
            ->select([
                'model',
                'settings',
            ])
            ->from([self::OLD_SETTINGS_TABLE])
            ->where([
                'model' => self::OLD_SETTINGS_MODEL,
            ])
            ->one();

        if (empty($oldSettings)) {
            Craft::warning('No shared settings found to migrate: ' . self::MODULE_ID);

            return;
        }

        // Prepare old settings for new settings format
        $newSettings = Json::decode($oldSettings['settings']);

        $newCoreSettings = [
            'alternateName' => $newSettings['pluginNameOverride'],
            'enabled' => true,
        ];

        unset($newSettings['pluginNameOverride']);

        Craft::$app->getProjectConfig()->set($moduleSettingsKey, $newSettings,
            "Update Sprout Settings for “{$moduleSettingsKey}”"
        );

        Craft::$app->getProjectConfig()->set($coreModuleSettingsKey, $newCoreSettings,
            "Update Sprout Core Settings for “{$coreModuleSettingsKey}”"
        );

        $this->delete(self::OLD_SETTINGS_TABLE, ['model' => self::OLD_SETTINGS_MODEL]);

        $this->deleteSettingsTableIfEmpty();
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }

    public function deleteSettingsTableIfEmpty(): void
    {
        $oldSettings = (new Query())
            ->select('*')
            ->from([self::OLD_SETTINGS_TABLE])
            ->all();

        if (empty($oldSettings)) {
            $this->dropTableIfExists(self::OLD_SETTINGS_TABLE);
        }
    }
}
