<?php

namespace BarrelStrength\Sprout\mailer\components\elements\audience\conditions;

use craft\elements\conditions\ElementCondition;

class AudienceCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            AudienceTypeConditionRule::class,
        ]);
    }
}
