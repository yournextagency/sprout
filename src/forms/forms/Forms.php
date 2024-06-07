<?php

namespace BarrelStrength\Sprout\forms\forms;

use BarrelStrength\Sprout\forms\components\elements\FormElement;
use BarrelStrength\Sprout\forms\db\SproutTable;
use BarrelStrength\Sprout\forms\FormsModule;
use BarrelStrength\Sprout\forms\integrations\Integration;
use Craft;
use craft\base\Field;
use craft\db\Query;
use yii\base\Component;

class Forms extends Component
{
    protected static array $formMetadataVariables = [];

    public array $activeSubmissions = [];

    /**
     *
     * Allows a user to add variables to an object that can be parsed by fields
     *
     * @example
     * {% do sprout.forms.addFormMetadataVariables({ submissionTitle: submission.title }) %}
     * {{ sprout.forms.displayForm('contact') }}
     */
    public static function addFormMetadataVariables(array $variables): void
    {
        static::$formMetadataVariables = array_merge(static::$formMetadataVariables, $variables);
    }

    public static function getFormMetadataVariables(): array
    {
        return static::$formMetadataVariables;
    }

    /**
     * Returns an array of models for forms found in the database
     *
     * @return FormElement[]
     */
    public function getAllForms(int $siteId = null): array
    {
        $query = FormElement::find();
        $query->siteId($siteId);
        $query->orderBy(['name' => SORT_ASC]);

        return $query->all();
    }

    /**
     * Returns a form model if one is found in the database by id
     */
    public function getFormById(int $formId, int $siteId = null): FormElement|null
    {
        $query = FormElement::find();
        $query->id($formId);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * Returns a form model if one is found in the database by handle
     */
    public function getFormByHandle(string $handle, int $siteId = null): FormElement|null
    {
        $query = FormElement::find();
        $query->handle($handle);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * Remove a field handle from title format
     */
    public function cleanTitleFormat(int $fieldId): ?string
    {
        /** @var Field $field */
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field) {
            $context = explode(':', $field->context);
            $formId = $context[1];

            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($formId);

            // Check if the field is in the titleformat
            if (str_contains($formRecord->titleFormat, $field->handle)) {
                // Let's remove the field from the titleFormat
                $newTitleFormat = preg_replace('/{' . $field->handle . '.*}/', '', $formRecord->titleFormat);
                $formRecord->titleFormat = $newTitleFormat;
                $formRecord->save(false);

                return $formRecord->titleFormat;
            }
        }

        return null;
    }

    /**
     * IF a field is updated, update the integrations
     */
    public function updateFieldOnIntegrations($oldHandle, $newHandle, $form): void
    {
        $integrations = FormsModule::getInstance()->formIntegrations->getIntegrationsByFormId($form->id);

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $integrationResult = (new Query())
                ->select(['id', 'settings'])
                ->from([SproutTable::FORM_INTEGRATIONS])
                ->where(['id' => $integration->id])
                ->one();

            if ($integrationResult === null) {
                continue;
            }

            $settings = json_decode($integrationResult['settings'], true, 512, JSON_THROW_ON_ERROR);

            $fieldMapping = $settings['fieldMapping'];
            foreach ($fieldMapping as $pos => $map) {
                if (isset($map['sourceFormField']) && $map['sourceFormField'] === $oldHandle) {
                    $fieldMapping[$pos]['sourceFormField'] = $newHandle;
                }
            }

            $integration->fieldMapping = $fieldMapping;
            FormsModule::getInstance()->formIntegrations->saveIntegration($integration);
        }
    }

    /**
     * Update a field handle with an new title format
     */
    public function updateTitleFormat(string $oldHandle, string $newHandle, string $titleFormat): string
    {
        return str_replace($oldHandle, $newHandle, $titleFormat);
    }

    /**
     * Checks if the current plugin edition allows a user to create a Form
     */
    public function canCreateForm(): bool
    {
        if (!FormsModule::isPro()) {
            $forms = $this->getAllForms();

            if (count($forms) >= 1) {
                return false;
            }
        }

        return true;
    }
}
