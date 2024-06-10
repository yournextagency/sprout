<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use BarrelStrength\Sprout\core\components\fieldlayoutelements\LightswitchField;
use Craft;
use craft\base\ElementInterface;

class PageTitlesField extends LightswitchField
{
    public string $attribute = 'displaySectionTitles';

    public ?string $name = 'formTypeSettings[displaySectionTitles]';

    public function getOnLabel(): ?string
    {
        return Craft::t('sprout-module-forms', 'Show');
    }

    public function getOffLabel(): ?string
    {
        return Craft::t('sprout-module-forms', 'Hide');
    }

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Page Titles');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Display Page Titles on Forms.');
    }
}
