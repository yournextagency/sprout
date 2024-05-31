<?php

namespace BarrelStrength\Sprout\core\db;

use craft\base\Plugin;
use craft\base\PluginInterface;
use craft\db\MigrationManager;
use yii\base\Module;

/**
 * Sprout Plugins using modules with schema must implement this interface
 */
interface SproutPluginMigrationInterface extends PluginInterface
{
    /**
     * The class names of the Sprout modules with schema in which this plugin depends on
     *
     * @example
     *
     * return [
     *   FormsModule::class,
     *   DataStudioModule::class,
     * ];
     */
    public static function getSchemaDependencies(): array;

    /**
     * Sprout Plugins using modules with schema must override the default
     * migrator method and return a SproutPluginMigrator class
     *
     * @return SproutPluginMigrator|MigrationManager
     */
    public function getMigrator(): SproutPluginMigrator|MigrationManager;
}
