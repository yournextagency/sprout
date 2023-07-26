<?php

namespace BarrelStrength\Sprout\transactional\components\mailers;

use BarrelStrength\Sprout\mailer\components\elements\email\EmailElement;
use BarrelStrength\Sprout\mailer\components\mailers\SystemMailerInstructionsSettingsTestSettings;

class TransactionalMailerInstructionsTestSettings extends SystemMailerInstructionsSettingsTestSettings
{
    public function getAdditionalTemplateVariables(EmailElement $email): array
    {
        $emailTypeSettings = $email->getEmailTypeSettings();
        $notificationEvent = $emailTypeSettings->getNotificationEvent($email);

        $emailTypeSettings->addAdditionalTemplateVariables(
            'object', $notificationEvent->getMockEventObject()
        );

        return $emailTypeSettings->getAdditionalTemplateVariables();
    }
}
