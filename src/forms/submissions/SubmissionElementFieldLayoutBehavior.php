<?php

namespace BarrelStrength\Sprout\forms\submissions;

use BarrelStrength\Sprout\forms\forms\FormBuilderHelper;
use craft\base\FieldLayoutElement;
use craft\models\FieldLayout;
use yii\base\Behavior;

/**
 * Extends Field Layout with additional Submission Element -specific behaviors
 *
 * @see BehaviorHelper::attachBehaviors() for initialization
 *
 * @property FieldLayout $owner
 */
class SubmissionElementFieldLayoutBehavior extends Behavior
{
    public function getFormBuilderConfig(): array
    {
        $layout = self::populateFormBuilderAttributes($this->owner);

        return $layout->getConfig();
    }

    public static function populateFormBuilderAttributes(FieldLayout $layout): FieldLayout
    {
        $tabs = $layout->getTabs();

        foreach ($tabs as $tab) {
            /** @var FieldLayoutElement $fieldLayoutElements */
            array_map(static function($fieldLayoutElement) {
                $field = $fieldLayoutElement->getField();

                $formFieldData = FormBuilderHelper::getFormFieldData($field);
                $fieldLayoutElement->formField = $formFieldData;

                $formFieldUiData = FormBuilderHelper::getFormFieldUiData($field);
                $fieldLayoutElement->formFieldUi = $formFieldUiData;

                return $fieldLayoutElement;
            }, $tab->getElements());
        }

        return $layout;
    }

    public function getCustomFieldsByUid(): array
    {
        $customFields = $this->owner->getCustomFields();

        $customFieldsByUid = [];
        foreach ($customFields as $customField) {
            $customFieldsByUid[$customField->uid] = $customField;
        }

        return $customFieldsByUid;
    }
}

