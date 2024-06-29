<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\TextField;

class SubmitButtonField extends TextField
{
    public string $attribute = 'submitButtonText';

    public ?string $name = 'formTypeSettings[submitButtonText]';

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Submit Button');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'The text displayed for the submit button.');
    }

    protected function selectorIcon(): ?string
    {
        return 'flag-checkered';
    }

    protected function value(?ElementInterface $element = null): mixed
    {
        return $element?->getFormType()?->submitButtonText;
    }
}
