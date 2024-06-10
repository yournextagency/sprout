<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use BarrelStrength\Sprout\core\components\fieldlayoutelements\LightswitchField;
use Craft;
use craft\base\ElementInterface;

class EnableCaptchasField extends LightswitchField
{
    public string $attribute = 'enableCaptchas';

    public ?string $name = 'formTypeSettings[enableCaptchas]';

    public function getOnLabel(): ?string
    {
        return Craft::t('sprout-module-forms', 'Enable');
    }

    public function getOffLabel(): ?string
    {
        return Craft::t('sprout-module-forms', 'Disable');
    }

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Enable Captchas');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Enable the globally configured captchas for this form.');
    }

    protected function selectorIcon(): ?string
    {
        return 'shield';
    }
}
