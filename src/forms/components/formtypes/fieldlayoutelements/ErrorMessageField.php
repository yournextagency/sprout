<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\TextareaField;

class ErrorMessageField extends TextareaField
{
    public string $attribute = 'messageOnError';

    public ?string $name = 'formTypeSettings[messageOnError]';

    public string|array|null $class = 'nicetext fullwidth';

    public ?int $rows = 5;

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Error Message');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'The message displayed when a form submission has errors. Leave blank for no message.');
    }

    public function getPlaceholder(): ?string
    {
        return Craft::t('sprout-module-forms', 'We were unable to process your submission. Please correct any errors and submit the form again.');
    }
}
