<?php

namespace BarrelStrength\Sprout\forms\formfields;

use craft\fieldlayoutelements\CustomField;

class CustomFormField extends CustomField
{
    // Disable Conditional behavior, for now
    protected function conditional(): bool
    {
        return false;
    }

    protected function settingsHtml(): ?string
    {
        $field = $this->getField();

        return $field->getSettingsHtml();
    }
}
