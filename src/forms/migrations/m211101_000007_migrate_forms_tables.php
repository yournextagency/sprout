<?php

namespace BarrelStrength\Sprout\forms\migrations;

use BarrelStrength\Sprout\core\components\fieldlayoutelements\LightswitchField;
use BarrelStrength\Sprout\uris\links\fieldlayoutelements\EnhancedLinkField;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\fieldlayoutelements\HorizontalRule;
use craft\fieldlayoutelements\TextareaField;
use craft\fieldlayoutelements\TextField;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\ProjectConfig;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;

/**
 * This migration must come after the Reports migration as
 * we need to insert default data source settings
 */
class m211101_000007_migrate_forms_tables extends Migration
{
    public const SPROUT_KEY = 'sprout';

    public const MODULE_ID = 'sprout-module-forms';

    public const FORMS_TABLE = '{{%sprout_forms}}';
    public const FORM_INTEGRATIONS_TABLE = '{{%sprout_form_integrations}}';
    public const FORM_INTEGRATIONS_LOG_TABLE = '{{%sprout_form_integrations_log}}';
    public const FORM_SUBMISSIONS_STATUSES_TABLE = '{{%sprout_form_submissions_statuses}}';
    public const FORM_SUBMISSIONS_TABLE = '{{%sprout_form_submissions}}';
    public const FORM_SUBMISSIONS_SPAM_LOG_TABLE = '{{%sprout_form_submissions_spam_log}}';

    public const OLD_FORMS_TABLE = '{{%sproutforms_forms}}';
    public const OLD_FORM_GROUPS_TABLE = '{{%sproutforms_formgroups}}';
    public const OLD_FORM_INTEGRATIONS_TABLE = '{{%sproutforms_integrations}}';
    public const OLD_FORM_INTEGRATIONS_LOG_TABLE = '{{%sproutforms_integrations_log}}';
    public const OLD_SUBMISSIONS_TABLE = '{{%sproutforms_entries}}';
    public const OLD_FORM_SUBMISSIONS_STATUSES_TABLE = '{{%sproutforms_entrystatuses}}';
    public const OLD_FORM_SUBMISSIONS_SPAM_LOG_TABLE = '{{%sproutforms_entries_spam_log}}';

    public const FORM_ELEMENT_CLASS = 'BarrelStrength\Sprout\forms\components\elements\FormElement';

    public function safeUp(): void
    {
        $cols = [
            'id',
            'name',
            'handle',
            'color',
            'sortOrder',
            'isDefault',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        if ($this->getDb()->tableExists(self::OLD_FORM_SUBMISSIONS_STATUSES_TABLE)) {
            $rows = (new Query())
                ->select($cols)
                ->from([self::OLD_FORM_SUBMISSIONS_STATUSES_TABLE])
                ->all();

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORM_SUBMISSIONS_STATUSES_TABLE, $cols, $rows)
                ->execute();
        }

        $cols = [
            'old_forms_table.id',
            'old_forms_table.fieldLayoutId', // Convert to submissionFieldLayout config
            'old_forms_table.name',
            'old_forms_table.handle',
            'old_forms_table.titleFormat',
            'old_forms_table.displaySectionTitles',
            'old_forms_table.redirectUri',
            'old_forms_table.submissionMethod',
            'old_forms_table.errorDisplayMethod',
            'old_forms_table.successMessage AS messageOnSuccess', // messageOnSuccess
            'old_forms_table.errorMessage AS messageOnError', // messageOnError
            'old_forms_table.submitButtonText',
            'old_forms_table.saveData',
            'old_forms_table.enableCaptchas',
            'old_forms_table.dateCreated',
            'old_forms_table.dateUpdated',
            'old_forms_table.uid',

            'old_forms_table.formTemplateId', // @todo - create form type and insert UID
            'elements_sites.siteId AS siteId',
        ];

        $colsNew = [
            'id',

            'name',
            'handle',
            'titleFormat',
            'displaySectionTitles',
            'redirectUri',
            'submissionMethod',
            'errorDisplayMethod',
            'messageOnSuccess', // successMessage
            'messageOnError', // errorMessage
            'submitButtonText',
            'saveData',
            'enableCaptchas',
            'dateCreated',
            'dateUpdated',
            'uid',

            'submissionFieldLayout', // Convert from fieldLayoutId and remove fieldLayoutId column
            'formTypeUid',
        ];

        if ($this->getDb()->tableExists(self::OLD_FORMS_TABLE)) {
            $rows = (new Query())
                ->select($cols)
                ->from(['old_forms_table' => self::OLD_FORMS_TABLE])
                ->innerJoin(['elements_sites' => Table::ELEMENTS_SITES],
                    '[[old_forms_table.id]] = [[elements_sites.elementId]]')
                ->all();

            $formTypes = [];

            // Duplicate rows so we can use all values queried from old table in content table migration
            $rowsForContentMigration = $rows;

            foreach ($rows as $key => $row) {

                $rows[$key]['submissionFieldLayout'] = null;

                if (isset($row['fieldLayoutId'])) {
                    $layoutId = $row['fieldLayoutId'];

                    // get field layout from project config
                    $layout = Craft::$app->getFields()->getLayoutById($layoutId);

                    if ($layout) {
                        // @todo - review. Is this all we need to do?
                        $rows[$key]['submissionFieldLayout'] = Json::encode($layout->getConfig());
                    }
                }

                if ($row['formTemplateId'] === 'BarrelStrength\Sprout\forms\components\formtypes\DefaultFormType') {
                    $defaultFormTypeUUID = StringHelper::UUID();

                    $formTypes[$defaultFormTypeUUID] = $this->getDefaultFormTypeFieldLayoutConfig(
                        'Default ' . StringHelper::substr($defaultFormTypeUUID, 1, 5),
                        $row,
                    );

                    $rows[$key]['formTemplateUid'] = $defaultFormTypeUUID;
                } else {
                    $formTemplate = $rows[$key]['formTemplateId'] ?? null;

                    $customTemplatesFormTypeUUID = StringHelper::UUID();

                    $formTypes[$customTemplatesFormTypeUUID] = $this->getCustomTemplatesFormTypeFieldLayoutConfig(
                        'Custom ' . StringHelper::substr($customTemplatesFormTypeUUID, 1, 5),
                        $formTemplate,
                    );

                    // Map custom template to formType settings and save settings
                    // Check if we already have a matching Form type....
                    $rows[$key]['formTemplateUid'] = $customTemplatesFormTypeUUID;
                }

                $oldRedirectUri = $row['redirectUri'] ?? null;

                if (filter_var($oldRedirectUri, FILTER_VALIDATE_URL)) {
                    $rows[$key]['redirectUri'] = Json::encode([
                        'url' => $oldRedirectUri,
                        'type' => 'BarrelStrength\Sprout\uris\components\links\AbsoluteUrl',
                    ]);
                } elseif (!empty($oldRedirectUri)) {
                    $rows[$key]['redirectUri'] = Json::encode([
                        'url' => $oldRedirectUri,
                        'type' => 'BarrelStrength\Sprout\uris\components\links\RelativeUrl',
                    ]);
                } else {
                    $rows[$key]['redirectUri'] = Json::encode([
                        'url' => null,
                        'type' => 'BarrelStrength\\Sprout\\uris\\components\\links\\CurrentPageUrl',
                    ]);
                }

                unset(
                    $rows[$key]['fieldLayoutId'],
                    $rows[$key]['formTemplateId'],
                    $rows[$key]['siteId'],
                );
            }

            if (!Craft::$app->getProjectConfig()->get('sprout.sprout-module-forms.formTypes')) {
                $formTypesConfig = ProjectConfig::packAssociativeArray($formTypes);
                Craft::$app->getProjectConfig()->set('sprout.sprout-module-forms.formTypes', $formTypesConfig);
            }

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORMS_TABLE, $colsNew, $rows)
                ->execute();

            $this->createFormContentTables($rowsForContentMigration);
        }

        $cols = [
            'id',
            'formId',
            'statusId',
            'ipAddress',
            'referrer',
            'userAgent',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        if ($this->getDb()->tableExists(self::OLD_SUBMISSIONS_TABLE)) {
            $rows = (new Query())
                ->select($cols)
                ->from([self::OLD_SUBMISSIONS_TABLE])
                ->all();

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORM_SUBMISSIONS_TABLE, $cols, $rows)
                ->execute();
        }

        $oldCols = [
            'id',
            'entryId',
            'type',
            'errors',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        $newCols = [
            'id',
            'submissionId', // entryId
            'type',
            'errors',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        if ($this->getDb()->tableExists(self::OLD_FORM_SUBMISSIONS_SPAM_LOG_TABLE)) {
            $rows = (new Query())
                ->select($oldCols)
                ->from([self::OLD_FORM_SUBMISSIONS_SPAM_LOG_TABLE])
                ->all();

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORM_SUBMISSIONS_SPAM_LOG_TABLE, $newCols, $rows)
                ->execute();
        }

        $cols = [
            'id',
            'formId',
            'name',
            'type',
            'sendRule',
            'settings',
            'enabled',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        if ($this->getDb()->tableExists(self::OLD_FORM_INTEGRATIONS_TABLE)) {
            $rows = (new Query())
                ->select($cols)
                ->from([self::OLD_FORM_INTEGRATIONS_TABLE])
                ->all();

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORM_INTEGRATIONS_TABLE, $cols, $rows)
                ->execute();
        }

        $oldCols = [
            'id',
            'entryId',
            'integrationId',
            'success',
            'status',
            'message',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        $newCols = [
            'id',
            'submissionId', // entryId
            'integrationId',
            'success',
            'status',
            'message',
            'dateCreated',
            'dateUpdated',
            'uid',
        ];

        if ($this->getDb()->tableExists(self::OLD_FORM_INTEGRATIONS_LOG_TABLE)) {
            $rows = (new Query())
                ->select($oldCols)
                ->from([self::OLD_FORM_INTEGRATIONS_LOG_TABLE])
                ->all();

            Craft::$app->getDb()->createCommand()
                ->batchInsert(self::FORM_INTEGRATIONS_LOG_TABLE, $newCols, $rows)
                ->execute();
        }
    }

    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }

    // @todo Does this work for migrating form content?
    public function createFormContentTables($formRows): void
    {
        foreach ($formRows as $form) {
            // If no form handle exists, keep moving
            if (!$formHandle = $form['handle'] ?? null) {
                continue;
            }

            if (!$formId = $form['id'] ?? null) {
                continue;
            }

            // create a new row in the {{%content}} table
            $now = Db::prepareDateForDb(DateTimeHelper::now());

            // Create a row in the content table for each element to support custom fields
            $this->insert(Table::CONTENT, [
                'elementId' => $form['id'],
                'siteId' => $form['siteId'],
                'dateCreated' => $now,
                'dateUpdated' => $now,
                'uid' => StringHelper::UUID(),
            ]);

            // Establish our old table and new table names
            $oldContentTable = "{{%sproutformscontent_$formHandle}}";
            $newContentTable = "{{%sprout_formcontent_$formId}}";

            // If the new table already exists, carry on
            if ($this->db->tableExists($newContentTable)) {
                continue;
            }

            // Simplify the old table by removing indices and foreign keys
            Db::dropAllForeignKeysToTable($oldContentTable);

            //            @todo - drop all indexes. Need to do so one by one.
            //            Db::dropIndexIfExists($oldContentTable);

            // Rename the old table to the the new table name
            Db::renameTable($oldContentTable, $newContentTable);

            $this->createIndex(null, $newContentTable, ['elementId', 'siteId'], true);
            $this->addForeignKey(null, $newContentTable, ['elementId'], Table::ELEMENTS, ['id'], 'CASCADE');
            $this->addForeignKey(null, $newContentTable, ['siteId'], Table::SITES, ['id'], 'CASCADE', 'CASCADE');

            $this->dropTableIfExists($oldContentTable);
        }
    }

    public function getDefaultFormTypeFieldLayoutConfig($name, $formSettings): array
    {
        $moduleSettingsKey = self::SPROUT_KEY . '.' . self::MODULE_ID;
        $projectConfigSettings = Craft::$app->getProjectConfig()->get($moduleSettingsKey) ?? [];

        //'old_forms_table.enableCaptchas',

        // plugin settings
        //      formTemplateId: barrelstrength\sproutforms\formtemplates\BasicTemplates

        $saveDataGlobal = $projectConfigSettings['saveDataByDefault'] ?? $projectConfigSettings['enableSaveDataDefaultValue'] ?? false;
        $isNotificationsTabEnabled = isset($projectConfigSettings['showNotificationsTab']) && !empty($projectConfigSettings['showNotificationsTab']) ? '1' : '';
        $isReportsTabEnabled = isset($projectConfigSettings['showReportsTab']) && !empty($projectConfigSettings['showReportsTab']) ? '1' : '';

        $config = [
            'type' => 'BarrelStrength\Sprout\forms\components\formtypes\DefaultFormType',
            'name' => $name,
            'formTemplate' => '@Sprout/TemplateRoot/forms/default',
            'formTemplateOverrideFolder' => null,
            'featureSettings' => [
                'BarrelStrength\Sprout\transactional\components\formfeatures\TransactionalFormFeature' => [
                    'enabled' => $isNotificationsTabEnabled,
                ],
                'BarrelStrength\Sprout\datastudio\components\formfeatures\DataStudioTabFormFeature' => [
                    'enabled' => $isReportsTabEnabled,
                ],
            ],
            'enabledFormFieldTypes' => [
                'BarrelStrength\Sprout\forms\components\formfields\SingleLineFormField',
                'BarrelStrength\Sprout\forms\components\formfields\ParagraphFormField',
                'BarrelStrength\Sprout\forms\components\formfields\MultipleChoiceFormField',
                'BarrelStrength\Sprout\forms\components\formfields\DropdownFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CheckboxesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\MultiSelectFormField',
                'BarrelStrength\Sprout\forms\components\formfields\FileUploadFormField',
                'BarrelStrength\Sprout\forms\components\formfields\DateFormField',
                'BarrelStrength\Sprout\forms\components\formfields\NumberFormField',
                'BarrelStrength\Sprout\forms\components\formfields\RegularExpressionFormField',
                'BarrelStrength\Sprout\forms\components\formfields\HiddenFormField',
                'BarrelStrength\Sprout\forms\components\formfields\InvisibleFormField',
                'BarrelStrength\Sprout\forms\components\formfields\NameFormField',
                'BarrelStrength\Sprout\forms\components\formfields\AddressFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EmailFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EmailDropdownFormField',
                'BarrelStrength\Sprout\forms\components\formfields\UrlFormField',
                'BarrelStrength\Sprout\forms\components\formfields\PhoneFormField',
                'BarrelStrength\Sprout\forms\components\formfields\OptInFormField',
                'BarrelStrength\Sprout\forms\components\formfields\GenderFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CategoriesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EntriesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\TagsFormField',
                'BarrelStrength\Sprout\forms\components\formfields\UsersFormField',
                'BarrelStrength\Sprout\forms\components\formfields\SectionHeadingFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CustomHtmlFormField',
                'BarrelStrength\Sprout\forms\components\formfields\PrivateNotesFormField',
            ],
            'submissionMethod' => $formSettings['submissionMethod'] ?? null,
            'errorDisplayMethod' => $formSettings['errorDisplayMethod'] ?? null,
            'saveData' => $formSettings['saveData'] ?? $saveDataGlobal ?? null,
            'trackRemoteIp' => $projectConfigSettings['trackRemoteIp'] ?? null,
            'allowedAssetVolumes' => $projectConfigSettings['allowedAssetVolumes'] ?? [],
            'defaultUploadLocationSubpath' => $projectConfigSettings['defaultUploadLocationSubpath'] ?? null,
            'enableEditSubmissionViaFrontEnd' => $projectConfigSettings['enableEditFormEntryViaFrontEnd'] ?? '',
        ];

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
            new TextField([
                'mandatory' => true,
                'label' => Craft::t('sprout-module-forms', 'Submit Button'),
                'instructions' => Craft::t('sprout-module-forms', 'The text displayed for the submit button.'),
                'attribute' => 'submitButtonText',
                'uid' => 'SPROUT-UID-FORMS-SUBMIT-BUTTON-TEXT-FIELD',
            ]),
            new EnhancedLinkField([
                'label' => Craft::t('sprout-module-forms', 'Redirect Page'),
                'instructions' => Craft::t('sprout-module-forms', 'Where should the user be redirected upon form submission? Leave blank to redirect user back to the form.'),
                'attribute' => 'redirectUri',
            ]),
            new HorizontalRule([
                'uid' => 'SPROUT-UID-FORMS-HORIZONTAL-RULE-SUBJECT-CONTENT-1',
            ]),
            new TextareaField([
                'label' => Craft::t('sprout-module-forms', 'Success Message'),
                'instructions' => Craft::t('sprout-module-forms', 'The message displayed after a submission is successfully submitted. Leave blank for no message.'),
                'placeholder' => Craft::t('sprout-module-forms', "Thanks! We'll be in touch."),
                'attribute' => 'messageOnSuccess',
                'class' => 'nicetext fullwidth',
                'rows' => 5,
                'mandatory' => true,
                'uid' => 'SPROUT-UID-FORMS-MESSAGE-ON-SUCCESS-FIELD',
            ]),
            new TextareaField([
                'label' => Craft::t('sprout-module-forms', 'Error Message'),
                'instructions' => Craft::t('sprout-module-forms', 'The message displayed when a form submission has errors. Leave blank for no message.'),
                'placeholder' => Craft::t('sprout-module-forms', 'We were unable to process your submission. Please correct any errors and submit the form again.'),
                'attribute' => 'messageOnError',
                'class' => 'nicetext fullwidth',
                'rows' => 5,
                'mandatory' => true,
                'uid' => 'SPROUT-UID-FORMS-MESSAGE-ON-ERROR-FIELD',
            ]),
            new HorizontalRule([
                'uid' => 'SPROUT-UID-FORMS-HORIZONTAL-RULE-SUBJECT-CONTENT-2',
            ]),
            new LightswitchField([
                'label' => Craft::t('sprout-module-forms', 'Page Titles'),
                'instructions' => Craft::t('sprout-module-forms', 'Display Page Titles on Forms.'),
                'attribute' => 'displaySectionTitles',
                'onLabel' => Craft::t('sprout-module-forms', 'Show'),
                'offLabel' => Craft::t('sprout-module-forms', 'Hide'),
                'uid' => 'SPROUT-UID-FORMS-PAGE-TITLES-FIELD',
            ]),
            new LightswitchField([
                'label' => Craft::t('sprout-module-forms', 'Enable Captchas'),
                'instructions' => Craft::t('sprout-module-forms', 'Enable the globally configured captchas for this form.'),
                'attribute' => 'enableCaptchas',
                'onLabel' => Craft::t('sprout-module-forms', 'Enable'),
                'offLabel' => Craft::t('sprout-module-forms', 'Disable'),
                'uid' => 'SPROUT-UID-FORMS-ENABLE-CAPTCHAS-FIELD',
            ]),
        ]);

        $fieldLayout->setTabs([
            $fieldLayoutTab,
        ]);

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }

    public function getCustomTemplatesFormTypeFieldLayoutConfig($name, $formTemplate): array
    {
        $config = [
            'type' => 'BarrelStrength\Sprout\forms\components\formtypes\CustomTemplatesFormType',
            'name' => $name,
            'formTemplate' => $formTemplate,
            'formTemplateOverrideFolder' => null,
            'featureSettings' => [
                'BarrelStrength\Sprout\transactional\components\formfeatures\TransactionalFormFeature' => [
                    'enabled' => '1',
                ],
                'BarrelStrength\Sprout\datastudio\components\formfeatures\DataStudioTabFormFeature' => [
                    'enabled' => '1',
                ],
            ],
            'enabledFormFieldTypes' => [
                'BarrelStrength\Sprout\forms\components\formfields\SingleLineFormField',
                'BarrelStrength\Sprout\forms\components\formfields\ParagraphFormField',
                'BarrelStrength\Sprout\forms\components\formfields\MultipleChoiceFormField',
                'BarrelStrength\Sprout\forms\components\formfields\DropdownFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CheckboxesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\MultiSelectFormField',
                'BarrelStrength\Sprout\forms\components\formfields\FileUploadFormField',
                'BarrelStrength\Sprout\forms\components\formfields\DateFormField',
                'BarrelStrength\Sprout\forms\components\formfields\NumberFormField',
                'BarrelStrength\Sprout\forms\components\formfields\RegularExpressionFormField',
                'BarrelStrength\Sprout\forms\components\formfields\HiddenFormField',
                'BarrelStrength\Sprout\forms\components\formfields\InvisibleFormField',
                'BarrelStrength\Sprout\forms\components\formfields\NameFormField',
                'BarrelStrength\Sprout\forms\components\formfields\AddressFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EmailFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EmailDropdownFormField',
                'BarrelStrength\Sprout\forms\components\formfields\UrlFormField',
                'BarrelStrength\Sprout\forms\components\formfields\PhoneFormField',
                'BarrelStrength\Sprout\forms\components\formfields\OptInFormField',
                'BarrelStrength\Sprout\forms\components\formfields\GenderFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CategoriesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\EntriesFormField',
                'BarrelStrength\Sprout\forms\components\formfields\TagsFormField',
                'BarrelStrength\Sprout\forms\components\formfields\UsersFormField',
                'BarrelStrength\Sprout\forms\components\formfields\SectionHeadingFormField',
                'BarrelStrength\Sprout\forms\components\formfields\CustomHtmlFormField',
                'BarrelStrength\Sprout\forms\components\formfields\PrivateNotesFormField',
            ],
        ];

        $fieldLayout = new FieldLayout([
            'type' => self::class,
        ]);

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }
}
