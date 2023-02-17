<?php

namespace BarrelStrength\Sprout\datastudio\migrations;

use BarrelStrength\Sprout\datastudio\DataStudioModule;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m211101_000001_migrate_settings_table_to_projectconfig extends Migration
{
    public const SPROUT_KEY = 'sprout';
    public const MODULE_ID = 'sprout-module-data-studio';
    public const OLD_SETTINGS_CLASS = 'barrelstrength\sproutbasereports\models\Settings';
    public const OLD_SETTINGS_TABLE = '{{%sprout_settings_craft3}}';

    public function safeUp(): void
    {
        $moduleSettingsKey = self::SPROUT_KEY . '.' . self::MODULE_ID;
        $coreSettingsKey = $moduleSettingsKey . '.modules.' . DataStudioModule::class;

        if (!$this->db->tableExists(self::OLD_SETTINGS_TABLE)) {
            return;
        }

        // Get shared sprout settings from old schema
        $oldSettings = (new Query())
            ->select([
                'model',
                'settings',
            ])
            ->from([self::OLD_SETTINGS_TABLE])
            ->where([
                'model' => self::OLD_SETTINGS_CLASS,
            ])
            ->one();

        if (empty($oldSettings)) {
            Craft::info('No shared settings found to migrate: ' . self::MODULE_ID);

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
            'Update Sprout Settings for: ' . self::MODULE_ID
        );

        Craft::$app->getProjectConfig()->set($coreSettingsKey, $newCoreSettings,
            'Update Sprout Core Settings for: ' . self::MODULE_ID
        );

        $this->delete(self::OLD_SETTINGS_TABLE, ['model' => self::OLD_SETTINGS_CLASS]);

        $this->deleteSettingsTableIfEmpty();
    }

    public function safeDown(): bool
    {
        echo "m211101_000001_migrate_settings_table_to_projectconfig cannot be reverted.\n";

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
