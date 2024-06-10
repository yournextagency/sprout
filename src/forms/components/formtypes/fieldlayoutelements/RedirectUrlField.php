<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use BarrelStrength\Sprout\uris\links\fieldlayoutelements\EnhancedLinkField;
use Craft;
use craft\base\ElementInterface;

class RedirectUrlField extends EnhancedLinkField
{
    public string $attribute = 'redirectUrl';

    public ?string $name = 'formTypeSettings[redirectUrl]';

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Redirect URL');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Where should the user be redirected upon form submission?');
    }

    protected function selectorIcon(): ?string
    {
        return 'sign-post';
    }
}
