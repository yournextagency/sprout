<?php

namespace BarrelStrength\Sprout\forms\submissions;

use BarrelStrength\Sprout\datastudio\datasources\DataSources;
use BarrelStrength\Sprout\forms\components\datasources\SubmissionsDataSource;
use BarrelStrength\Sprout\forms\components\notificationevents\SaveSubmissionNotificationEvent;
use BarrelStrength\Sprout\forms\forms\Forms;
use BarrelStrength\Sprout\forms\FormsModule;
use BarrelStrength\Sprout\transactional\notificationevents\NotificationEvents;
use craft\events\RegisterComponentTypesEvent;
use Craft;

class SubmissionsHelper
{
    public static function getEnabledFormMetadataSettings(): array
    {
        $settings = FormsModule::getInstance()->getSettings();

        foreach ($settings->formMetadata as $metadataSettings) {
            if ($metadataSettings['enabled']) {
                $enabledFormMetadata[$metadataSettings['label']] = $metadataSettings['metadatumFormat'];
            }
        }

        return $enabledFormMetadata ?? [];
    }

    public static function setFormMetadataSessionVariable(int $formId): void
    {
        $enabledFormMetadata = self::getEnabledFormMetadataSettings();

        $formMetadataValues = [];
        foreach ($enabledFormMetadata as $key => $value) {
            $formMetadataValues[$key] = Craft::$app->getView()->renderObjectTemplate(
                $value,
                Forms::getFieldVariables()
            );
        }

        Craft::$app->getSession()->set('formMetadata:' .$formId, $formMetadataValues);
    }

    public static function getFormMetadataSessionVariable(int $formId): array
    {
        return Craft::$app->getSession()->get('formMetadata:' .$formId) ?? [];
    }
}
