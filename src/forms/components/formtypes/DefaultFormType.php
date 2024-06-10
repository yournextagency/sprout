<?php

namespace BarrelStrength\Sprout\forms\components\formtypes;

use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\EnableCaptchasField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\ErrorMessageField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\PageTitlesField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\RedirectUrlField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\SubmitButtonField;
use BarrelStrength\Sprout\forms\components\formtypes\fieldlayoutelements\SuccessMessageField;
use BarrelStrength\Sprout\forms\formtypes\FormType;
use Craft;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\fieldlayoutelements\HorizontalRule;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;

class DefaultFormType extends FormType
{
    public static function displayName(): string
    {
        return Craft::t('sprout-module-forms', 'Default Templates');
    }

    public function getHandle(): ?string
    {
        return 'default';
    }

    public function getDefaultTemplatesFolder(): ?string
    {
        return '@Sprout/TemplateRoot/form/default';
    }

    public static function defineNativeFields(DefineFieldLayoutFieldsEvent $event): void
    {
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $event->sender;

        if ($fieldLayout->type === self::class) {
            $event->fields[RedirectUrlField::class] = RedirectUrlField::class;
            $event->fields[SubmitButtonField::class] = SubmitButtonField::class;
            $event->fields[SuccessMessageField::class] = SuccessMessageField::class;
            $event->fields[ErrorMessageField::class] = ErrorMessageField::class;
            $event->fields[PageTitlesField::class] = PageTitlesField::class;
            $event->fields[EnableCaptchasField::class] = EnableCaptchasField::class;
        }
    }

    public function createFieldLayout(): ?FieldLayout
    {
        $fieldLayout = new FieldLayout([
            'type' => self::class,
        ]);

        $fieldLayoutTab = new FieldLayoutTab([
            'layout' => $fieldLayout,
            'name' => Craft::t('sprout-module-forms', 'Templates'),
            'sortOrder' => 1,
            'uid' => StringHelper::UUID(),
        ]);

        $fieldLayoutTab->setElements([
            new SubmitButtonField([
                'mandatory' => true,
            ]),
            new RedirectUrlField([
                'mandatory' => true,
            ]),
            new HorizontalRule([
                'uid' => 'SPROUT-UID-FORMS-HORIZONTAL-RULE-SUBJECT-CONTENT-1',
            ]),
            new SuccessMessageField([
                'mandatory' => true,
            ]),
            new ErrorMessageField([
                'mandatory' => true,
            ]),
            new HorizontalRule([
                'uid' => 'SPROUT-UID-FORMS-HORIZONTAL-RULE-SUBJECT-CONTENT-2',
            ]),
            new PageTitlesField([
                'mandatory' => true,
            ]),
            new EnableCaptchasField([
                'mandatory' => true,
            ]),
        ]);

        $fieldLayout->setTabs([
            $fieldLayoutTab,
        ]);

        return $this->_fieldLayout = $fieldLayout;
    }
}
