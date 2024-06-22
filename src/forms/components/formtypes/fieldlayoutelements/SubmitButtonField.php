<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use BarrelStrength\Sprout\uris\links\fieldlayoutelements\EnhancedLinkField;
use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\TextField;
use craft\fields\PlainText;
use craft\helpers\StringHelper;

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
