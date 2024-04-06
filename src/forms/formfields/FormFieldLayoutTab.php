<?php

namespace BarrelStrength\Sprout\forms\formfields;

use craft\models\FieldLayoutTab;

class FormFieldLayoutTab extends FieldLayoutTab
{
    // Disable Conditional behavior, for now
    protected function conditional(): bool
    {
        return false;
    }
}
