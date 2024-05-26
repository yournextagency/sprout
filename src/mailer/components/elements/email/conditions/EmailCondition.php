<?php

namespace BarrelStrength\Sprout\mailer\components\elements\email\conditions;

use craft\elements\conditions\ElementCondition;

abstract class EmailCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            EmailTypeConditionRule::class,
            PreheaderTextConditionRule::class,
            SubjectLineConditionRule::class,
        ]);
    }
}
