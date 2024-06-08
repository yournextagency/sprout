<?php

namespace BarrelStrength\Sprout\forms\formtypes;

use BarrelStrength\Sprout\forms\components\elements\FormElement;
use BarrelStrength\Sprout\forms\FormsModule;
use BarrelStrength\Sprout\mailer\emailtypes\EmailTypeHelper;
use Craft;
use craft\base\FieldLayoutProviderInterface;
use craft\base\SavableComponent;
use craft\models\FieldLayout;

abstract class FormType extends SavableComponent implements FormTypeInterface, FieldLayoutProviderInterface
{
    public ?string $name = null;

    public ?string $handle = null;

    public ?string $customTemplatesFolder = null;

    public array $featureSettings = [];

    public ?string $defaultEmailTypeUid = null;

    public array $enabledFormFieldTypes = [];

    public ?string $submissionMethod = null;

    public ?string $errorDisplayMethod = null;

    public bool $enableSaveData = true;

    public array|string $allowedAssetVolumes = [];

    public ?string $defaultUploadLocationSubpath = null;

    public bool $enableEditSubmissionViaFrontEnd = false;

    public ?FormElement $form = null;

    protected ?FieldLayout $_fieldLayout = null;

    public ?string $uid = null;

    public function getIncludeTemplates(): array
    {
        return [
            Craft::getAlias($this->getCustomTemplatesFolder()),
            $this->getRenderTemplatesFolder(),
            Craft::getAlias($this->getDefaultTemplatesFolder()),
        ];
    }

    public function getCustomTemplatesFolder(): ?string
    {
        return $this->customTemplatesFolder;
    }

    public function getRenderTemplatesFolder(): ?string
    {
        $generalConfig = Craft::$app->getConfig()->getGeneral();

        return $generalConfig->partialTemplatesPath . DIRECTORY_SEPARATOR . FormElement::refHandle() . DIRECTORY_SEPARATOR . $this->handle;
    }

    public function getDefaultTemplatesFolder(): ?string
    {
        return null;
    }

    /**
     * Adds pre-defined options for css classes.
     *
     * These classes will display in the CSS Classes dropdown list on the Field Edit modal
     * for Field Types that support the $cssClasses property.
     */
    public function getCssClassDefaults(): array
    {
        return [];
    }

    public function createFieldLayout(): ?FieldLayout
    {
        return null;
    }

    public function getFieldLayout(): FieldLayout
    {
        if ($this->_fieldLayout) {
            return $this->_fieldLayout;
        }

        $this->_fieldLayout = $this->createFieldLayout();

        return $this->_fieldLayout;
    }

    public function setFieldLayout(?FieldLayout $fieldLayout): void
    {
        $this->_fieldLayout = $fieldLayout;
    }

    public function getFormFieldTypesByType(): array
    {
        if (empty($this->enabledFormFieldTypes)) {
            // Default to all
            return FormsModule::getInstance()->formFields->getFormFieldTypes();
        }

        return array_combine($this->enabledFormFieldTypes, array_fill_keys($this->enabledFormFieldTypes, true));
    }

    public function getEmailTypesOptions(): array
    {
        return EmailTypeHelper::getEmailTypesOptions();
    }

    public function getFormFieldFeatures(): array
    {
        $formFieldGroups = FormsModule::getInstance()->formFields->getDefaultFormFieldTypesByGroup();

        $options = [];

        foreach ($formFieldGroups as $formFieldGroupKey => $formFields) {
            foreach ($formFields as $formFieldType) {
                // add label/value keys to options
                $options[$formFieldGroupKey][$formFieldType] = $formFieldType::displayName();
            }
        }

        return $options;
    }

    public function getConfig(): array
    {
        $config = [
            'type' => static::class,
            'name' => $this->name,
            'handle' => $this->handle,
            'customTemplatesFolder' => $this->customTemplatesFolder,
            'featureSettings' => $this->featureSettings,
            'enabledFormFieldTypes' => $this->enabledFormFieldTypes,
            'submissionMethod' => $this->submissionMethod,
            'errorDisplayMethod' => $this->errorDisplayMethod,
            'allowedAssetVolumes' => $this->allowedAssetVolumes,
            'defaultUploadLocationSubpath' => $this->defaultUploadLocationSubpath,
            'enableEditSubmissionViaFrontEnd' => $this->enableEditSubmissionViaFrontEnd,
        ];

        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }
}
