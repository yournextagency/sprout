<?php

namespace BarrelStrength\Sprout\forms\components\formtypes;

use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\EnableCaptchasField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\RedirectUrlField;
use BarrelStrength\Sprout\forms\formtypes\FormType;
use Craft;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\models\FieldLayout;

/**
 * The CustomFormType is used to dynamically create a FormType
 * integration when a user selects the custom option and provides a path
 * to the custom templates they wish to use.
 *
 * The CustomFormType integration is not registered with Sprout Forms
 * and will not display in the Form Types dropdown list.
 *
 * @property string $path
 */
class CustomTemplatesFormType extends FormType
{
    public static function displayName(): string
    {
        return Craft::t('sprout-module-forms', 'Custom Templates');
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public static function defineNativeFields(DefineFieldLayoutFieldsEvent $event): void
    {
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $event->sender;

        if ($fieldLayout->type === self::class) {
            $event->fields[] = RedirectUrlField::class;
            $event->fields[] = EnableCaptchasField::class;
        }
    }

    public function createFieldLayout(): ?FieldLayout
    {
        return new FieldLayout([
            'type' => self::class,
        ]);
    }
}
