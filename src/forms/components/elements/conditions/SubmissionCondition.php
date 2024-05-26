<?php

namespace BarrelStrength\Sprout\forms\components\elements\conditions;

use craft\elements\conditions\ElementCondition;

class SubmissionCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            SubmissionFormConditionRule::class,
        ]);
    }
}
