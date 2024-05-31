<?php

namespace BarrelStrength\Sprout\transactional\notificationevents;

use yii\base\Event;

interface ElementEventInterface
{
    public static function conditionType(): string;

    public static function elementType(): string;

    /**
     * Gives an Element Event a chance to reserve query params in use
     * so that conflicting rules don't display in the Condition Builder
     */
    public function getExclusiveQueryParams(): array;

    public function matchNotificationEvent(Event $event): bool;
}
