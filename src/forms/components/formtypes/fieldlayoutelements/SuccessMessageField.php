<?php

namespace BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\TextareaField;
use craft\fieldlayoutelements\TextField;

class SuccessMessageField extends TextareaField
{
    public string $attribute = 'messageOnSuccess';

    public ?string $name = 'formTypeSettings[messageOnSuccess]';

    public string|array|null $class = 'nicetext fullwidth';

    public ?int $rows = 5;

    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'Success Message');
    }

    protected function defaultInstructions(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::t('sprout-module-forms', 'The message displayed after a submission is successfully submitted. Leave blank for no message.');
    }

    public function getPlaceholder(): ?string
    {
        return Craft::t('sprout-module-forms', 'Thank you for your submission.');
    }

    protected function selectorIcon(): ?string
    {
        return 'thumbs-up';
    }
}
