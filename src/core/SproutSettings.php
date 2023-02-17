<?php

namespace BarrelStrength\Sprout\core;

use Craft;
use craft\config\BaseConfig;

class SproutSettings extends BaseConfig
{
    public const ROOT_PROJECT_CONFIG_KEY = 'sprout';

    public const SITE_TEMPLATE_ROOT = 'sprout';

    public array $modules = [];

    public function modules(array $value): self
    {
        $this->modules = $value;

        return $this;
    }

    public function getCpSettingsRows(): array
    {
        $modules = Sprout::getInstance()->coreModules->getAvailableModules();

        $cpSettingsRows = [];

        foreach ($modules as $module) {
            $projectConfigSettings = $this->modules[$module] ?? null;

            $enabledValue = (isset($projectConfigSettings['enabled']) && !empty($projectConfigSettings['enabled'])) ? $projectConfigSettings['enabled'] : false;
            $alternateNameValue = (isset($projectConfigSettings['alternateName']) && !empty($projectConfigSettings['alternateName'])) ? $projectConfigSettings['alternateName'] : '';

            $enabledInputHtml = Craft::$app->getView()->renderTemplate('_includes/forms/lightswitch', [
                'name' => 'modules[' . $module . '][enabled]',
                'on' => $enabledValue,
                'small' => true,
            ]);

            $infoHtml = '&nbsp;<span class="info">' . $module::getDescription() . '</span>';

            if ($module::hasEditions() && $module::isPro()) {
                $editionHtml = '<span class="sprout-pro">PRO</span>';
            } elseif ($module::hasEditions()) {
                $editionHtml = '<span class="sprout-lite">LITE</span>';
                $editionHtml .= '&nbsp;<span class="info">' . $module::getUpgradeMessage() . '</span>';
            } else {
                $editionHtml = '';
            }

            $cpSettingsRows[$module] = [
                'heading' => $module::getDisplayName() . $infoHtml,
                'enabled' => $enabledInputHtml,
                'alternateName' => $alternateNameValue,
                'edition' => $editionHtml,
            ];
        }

        return $cpSettingsRows;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['modules'], 'required'];

        return $rules;
    }
}

