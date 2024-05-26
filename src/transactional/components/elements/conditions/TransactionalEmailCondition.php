<?php

namespace BarrelStrength\Sprout\transactional\components\elements\conditions;

use BarrelStrength\Sprout\mailer\components\elements\email\conditions\EmailCondition;

class TransactionalEmailCondition extends EmailCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            NotificationEventConditionRule::class,
        ]);
    }
}
